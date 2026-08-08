<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Transaksi;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Admin HANYA bisa melihat & mengubah status pesanan di sini.
     * Tidak ada create/edit/delete transaksi oleh admin — transaksi
     * murni dibuat oleh customer sendiri lewat proses checkout.
     */
    public function index(Request $request)
    {
        $query = Transaksi::with(['pelanggan', 'platform']);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  // Cari dari nama_pelanggan yang tersimpan di transaksi (snapshot
                  // saat transaksi dibuat), bukan dari data pelanggan yang
                  // terkini/sudah berubah.
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('platform')) {
            $query->where('platform_id', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('status_pesanan', $request->status);
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_transaksi', $request->tanggal);
        }

        $transaksis = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $platforms = Platform::orderBy('nama_platform')->get();

        return view('transaksi.index', compact('transaksis', 'platforms'));
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'platform', 'detailTransaksis.produk'])->findOrFail($id);
        return view('transaksi.show', compact('transaksi'));
    }

    // Satu-satunya aksi yang boleh dilakukan admin terhadap transaksi:
    // mengubah status pesanan (Pending/Proses/Dikirim/Selesai/Batal).
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => 'required|in:Pending,Proses,Dikirim,Selesai,Batal',
            'alasan_pembatalan' => 'required_if:status_pesanan,Batal|nullable|string|max:500',
        ], [
            'alasan_pembatalan.required_if' => 'Alasan pembatalan wajib diisi.',
        ]);

        $transaksi = Transaksi::with('detailTransaksis')->findOrFail($id);

        // Pesanan yang sudah dibatalkan customer sendiri atau otomatis oleh
        // sistem (kadaluarsa) itu final — admin tidak boleh mengubah status
        // pesanan ini lagi. Kalau admin sendiri yang membatalkan, tetap boleh.
        if ($transaksi->statusPesananTerkunci()) {
            return redirect()->back()->withErrors([
                'status_pesanan' => 'Pesanan ini sudah dibatalkan oleh ' .
                    ($transaksi->dibatalkan_oleh === 'customer' ? 'customer' : 'sistem (kadaluarsa)') .
                    ' dan tidak bisa diubah statusnya lagi.',
            ]);
        }

        // Gak boleh mulai diproses kalau pembayaran belum dikonfirmasi Lunas.
        // Ini yang tadinya cuma diandalkan ke ingatan admin, sekarang dicegah sistem.
        if ($request->status_pesanan === 'Proses' && $transaksi->status_pembayaran !== Transaksi::STATUS_LUNAS) {
            return redirect()->back()->withErrors([
                'status_pesanan' => 'Pesanan belum bisa diproses karena pembayaran belum dikonfirmasi Lunas.',
            ]);
        }

        if ($request->status_pesanan === 'Batal') {
            // Pakai method bersama supaya stok yang sudah dipotong saat checkout
            // ikut dikembalikan, sama seperti kalau customer/auto-batal yang memicu.
            $transaksi->batalkanDanKembalikanStok('admin', $request->alasan_pembatalan);

            if ($transaksi->pelanggan_id) {
                Notifikasi::buatUntukPelanggan(
                    $transaksi->pelanggan_id,
                    'pesanan_dibatalkan_admin',
                    'Pesanan Dibatalkan Admin',
                    "{$transaksi->kode_transaksi} dibatalkan oleh admin. Alasan: {$request->alasan_pembatalan}",
                    $transaksi->id
                );
            }
        } else {
            $transaksi->update([
                'status_pesanan' => $request->status_pesanan
            ]);

            if ($transaksi->pelanggan_id && in_array($request->status_pesanan, ['Proses', 'Dikirim'])) {
                $tipe = $request->status_pesanan === 'Proses' ? 'pesanan_diproses' : 'pesanan_dikirim';
                $judul = $request->status_pesanan === 'Proses' ? 'Pesanan Sedang Diproses' : 'Pesanan Dikirim';
                $pesan = $request->status_pesanan === 'Proses'
                    ? "{$transaksi->kode_transaksi} sedang diproses oleh admin."
                    : "{$transaksi->kode_transaksi} sudah dikirim. Cek halaman pesanan untuk detailnya.";

                Notifikasi::buatUntukPelanggan($transaksi->pelanggan_id, $tipe, $judul, $pesan, $transaksi->id);
            }
        }

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    public function updateStatusPembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:Lunas,Ditolak',
            'catatan_pembayaran' => 'nullable|string|max:255',
        ]);

        $transaksi = Transaksi::findOrFail($id);

        $transaksi->update([
            'status_pembayaran' => $request->status_pembayaran,
            'catatan_pembayaran' => $request->status_pembayaran === 'Ditolak'
                ? $request->catatan_pembayaran
                : null,
        ]);

        if ($request->status_pembayaran === 'Ditolak' && $transaksi->pelanggan_id) {
            Notifikasi::buatUntukPelanggan(
                $transaksi->pelanggan_id,
                'pembayaran_ditolak',
                'Pembayaran Ditolak',
                "Bukti pembayaran {$transaksi->kode_transaksi} ditolak" .
                    ($request->catatan_pembayaran ? ": {$request->catatan_pembayaran}" : '.') .
                    ' Silakan unggah ulang bukti pembayaran.',
                $transaksi->id
            );
        }

        $pesan = $request->status_pembayaran === 'Lunas'
            ? 'Pembayaran dikonfirmasi lunas.'
            : 'Pembayaran ditolak, pelanggan diminta upload ulang bukti transfer.';

        return redirect()->back()->with('success', $pesan);
    }
}
