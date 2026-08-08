<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Transaksi;
use App\Models\Notifikasi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\Platform;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    // Ambil data pelanggan milik user yang sedang login.
    private function pelangganAktif(): Pelanggan
    {
        $user = auth()->user();

        return Pelanggan::firstOrCreate(
            ['nama_pelanggan' => $user->name],
            ['jenis_kelamin' => 'Lainnya', 'kota' => 'Online']
        );
    }

    public function katalog()
    {
        $produks = Produk::with('varians')->orderBy('nama_produk')->get();
        $ewalletFavorit = $this->pelangganAktif()->ewallet_favorit;
        return view('customer.katalog', compact('produks', 'ewalletFavorit'));
    }

    public function promo()
    {
        // Cuma produk yang lagi didiskon (diskon_persen > 0) oleh admin
        $produks = Produk::with('varians')->promo()->orderByDesc('diskon_persen')->get();
        $ewalletFavorit = $this->pelangganAktif()->ewallet_favorit;
        return view('customer.promo', compact('produks', 'ewalletFavorit'));
    }

    public function checkout(Request $request)
    {
        $pelangganCek = $this->pelangganAktif();

        if (empty($pelangganCek->kota) || empty($pelangganCek->alamat)) {
            return redirect()->route('customer.profil.edit')->withErrors([
                'profil' => 'Lengkapi kota dan alamat di profil kamu dulu sebelum checkout.',
            ]);
        }

        $request->validate([
            'produk_id'          => 'required|exists:produks,id',
            'kuantitas'          => 'required|integer|min:1',
            'ukuran'             => 'required|string',
            'warna'              => 'required|in:' . implode(',', Produk::WARNA_OPTIONS),
            'metode_pembayaran'  => 'required|in:Transfer Bank,E-Wallet',
            'ewallet_provider'   => 'required_if:metode_pembayaran,E-Wallet|nullable|in:' . implode(',', Pelanggan::EWALLET_OPTIONS),
        ], [
            'ewallet_provider.required_if' => 'Pilih provider e-wallet-nya dulu ya.',
        ]);

        $pelanggan = $this->pelangganAktif();

        $platform = Platform::where('nama_platform', 'Website')->first();
        $platformId = $platform ? $platform->id : 1;

        // Ambil data produk utama
        $produk = Produk::whereKey($request->produk_id)->firstOrFail();

        // Kunci baris varian spesifik (ukuran + warna) supaya aman dari race condition saat stok tipis
        $varian = ProdukVarian::where('produk_id', $request->produk_id)
            ->where('ukuran', $request->ukuran)
            ->where('warna', $request->warna)
            ->lockForUpdate()
            ->first();

        $stokTersedia = $varian ? $varian->stok : 0;

        if (!$varian || $stokTersedia < $request->kuantitas) {
            return back()->withInput()->withErrors([
                'kuantitas' => "Stok \"{$produk->nama_produk}\" ukuran {$request->ukuran} warna {$request->warna} tinggal {$stokTersedia}.",
            ]);
        }

        // Susun label metode pembayaran final.
        $metodePembayaran = $request->metode_pembayaran === 'E-Wallet'
            ? 'E-Wallet - ' . $request->ewallet_provider
            : $request->metode_pembayaran;

        // Simpan/ganti e-wallet favorit pelanggan otomatis
        if ($request->metode_pembayaran === 'E-Wallet') {
            $pelanggan->update(['ewallet_favorit' => $request->ewallet_provider]);
        }

        $statusPembayaranAwal = Transaksi::STATUS_MENUNGGU_PEMBAYARAN;

        // Hitung harga pakai diskon promo kalau produknya lagi promo
        $hargaSatuanAwal = $produk->harga_satuan;
        $diskonPerUnit = (int) round($hargaSatuanAwal * $produk->diskon_persen / 100);
        $diskonTotal = $diskonPerUnit * $request->kuantitas;
        $subtotal = ($hargaSatuanAwal * $request->kuantitas) - $diskonTotal;
        $biayaPengiriman = 10000;
        $grandTotal = $subtotal + $biayaPengiriman;

        $transaksi = DB::transaction(function () use ($request, $pelanggan, $platformId, $produk, $varian, $metodePembayaran, $statusPembayaranAwal, $hargaSatuanAwal, $diskonTotal, $subtotal, $biayaPengiriman, $grandTotal) {
            $transaksi = Transaksi::create([
                'kode_transaksi'     => Transaksi::generateKodeTransaksi(),
                'pelanggan_id'       => $pelanggan->id,
                'nama_pelanggan'     => $pelanggan->nama_pelanggan,
                'jenis_kelamin'      => $pelanggan->jenis_kelamin,
                'kota'               => $pelanggan->kota,
                'alamat'             => $pelanggan->alamat,
                'platform_id'        => $platformId,
                'tanggal_transaksi'  => now(),
                'metode_pembayaran'  => $metodePembayaran,
                'status_pesanan'     => 'Pending',
                'status_pembayaran'  => $statusPembayaranAwal,
                'biaya_pengiriman'   => $biayaPengiriman,
                'total_diskon'       => $diskonTotal,
                'grand_total'        => $grandTotal,
            ]);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id'    => $produk->id,
                'ukuran'       => $request->ukuran,
                'warna'        => $request->warna,
                'kuantitas'    => $request->kuantitas,
                'harga_satuan' => $hargaSatuanAwal,
                'diskon'       => $diskonTotal,
                'subtotal'     => $subtotal,
            ]);

            // Kurangi stok pada VARIAN spesifik (bukan tabel produks)
            $varian->decrement('stok', $request->kuantitas);

            return $transaksi;
        });

        Notifikasi::buatUntukAdmin(
            'pesanan_baru',
            'Pesanan Baru',
            "{$transaksi->kode_transaksi} dari {$pelanggan->nama_pelanggan} senilai Rp " . number_format($transaksi->grand_total, 0, ',', '.'),
            $transaksi->id
        );

        return redirect()->route('customer.pesanan.show', $transaksi->id)
            ->with('success', 'Pesanan Anda berhasil dibuat! Silakan selesaikan pembayaran di bawah ini.');
    }

    public function pesananIndex()
    {
        $pelanggan = $this->pelangganAktif();

        $pesanans = Transaksi::with(['detailTransaksis.produk', 'platform'])
            ->where('pelanggan_id', $pelanggan->id)
            ->latest()
            ->get();

        return view('customer.pesanan_index', compact('pesanans'));
    }

    public function pesananShow($id)
    {
        $pelanggan = $this->pelangganAktif();

        $pesanan = Transaksi::with(['detailTransaksis.produk', 'platform', 'pelanggan'])
            ->where('pelanggan_id', $pelanggan->id)
            ->findOrFail($id);

        return view('customer.pesanan_show', compact('pesanan'));
    }

    public function batalkanPesanan(Request $request, $id)
    {
        $pelanggan = $this->pelangganAktif();

        $pesanan = Transaksi::with('detailTransaksis')
            ->where('pelanggan_id', $pelanggan->id)
            ->findOrFail($id);

        if (!$pesanan->bolehDibatalkanCustomer()) {
            return back()->withErrors([
                'batal' => 'Pesanan ini sudah tidak bisa dibatalkan sendiri. Hubungi admin kalau perlu bantuan.',
            ]);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:500',
        ], [
            'alasan_pembatalan.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $pesanan->batalkanDanKembalikanStok('customer', $request->alasan_pembatalan);

        Notifikasi::buatUntukAdmin(
            'pesanan_dibatalkan_customer',
            'Pesanan Dibatalkan Customer',
            "{$pesanan->kode_transaksi} dibatalkan oleh {$pelanggan->nama_pelanggan}. Alasan: {$request->alasan_pembatalan}",
            $pesanan->id
        );

        return redirect()->route('customer.pesanan.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function konfirmasiDiterima($id)
    {
        $pelanggan = $this->pelangganAktif();

        $pesanan = Transaksi::where('pelanggan_id', $pelanggan->id)->findOrFail($id);

        if (!$pesanan->bolehKonfirmasiDiterima()) {
            return back()->withErrors([
                'status' => 'Pesanan ini belum berstatus Dikirim.',
            ]);
        }

        $pesanan->update(['status_pesanan' => 'Selesai']);

        Notifikasi::buatUntukAdmin(
            'pesanan_selesai_customer',
            'Pesanan Selesai',
            "{$pesanan->kode_transaksi} dikonfirmasi diterima oleh {$pelanggan->nama_pelanggan}.",
            $pesanan->id
        );

        return back()->with('success', 'Terima kasih! Pesanan ditandai selesai.');
    }

    public function uploadBuktiPembayaran(Request $request, $id)
    {
        $pelanggan = $this->pelangganAktif();

        $pesanan = Transaksi::where('pelanggan_id', $pelanggan->id)->findOrFail($id);

        $metodeBolehUploadBukti = str_starts_with($pesanan->metode_pembayaran, 'Transfer Bank')
            || str_starts_with($pesanan->metode_pembayaran, 'E-Wallet');

        if (!$metodeBolehUploadBukti) {
            return back()->withErrors(['bukti_pembayaran' => 'Upload bukti pembayaran hanya untuk pesanan dengan metode Transfer Bank atau E-Wallet.']);
        }

        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($pesanan->bukti_pembayaran && Storage::disk('public')->exists($pesanan->bukti_pembayaran)) {
            Storage::disk('public')->delete($pesanan->bukti_pembayaran);
        }

        $path = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $pesanan->update([
            'bukti_pembayaran' => $path,
            'status_pembayaran' => Transaksi::STATUS_MENUNGGU_VERIFIKASI,
            'catatan_pembayaran' => null,
        ]);

        Notifikasi::buatUntukAdmin(
            'pembayaran_diunggah',
            'Bukti Pembayaran Diunggah',
            "{$pesanan->kode_transaksi} dari {$pesanan->nama_pelanggan} sudah mengunggah bukti pembayaran, perlu diverifikasi.",
            $pesanan->id
        );

        return back()->with('success', 'Bukti transfer berhasil diunggah. Admin akan memverifikasi pembayaranmu.');
    }

    public function editProfil()
    {
        $pelanggan = $this->pelangganAktif();
        return view('customer.profil', compact('pelanggan'));
    }

    public function updateProfil(Request $request)
    {
        $request->validate([
            'jenis_kelamin'    => 'required|in:Pria,Wanita,Lainnya',
            'kota'             => 'required|string|max:255',
            'alamat'           => 'required|string|max:1000',
            'ewallet_favorit'  => 'nullable|in:' . implode(',', Pelanggan::EWALLET_OPTIONS),
        ]);

        $pelanggan = $this->pelangganAktif();
        $pelanggan->update($request->only(['jenis_kelamin', 'kota', 'alamat', 'ewallet_favorit']));

        return redirect()->route('customer.profil.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}