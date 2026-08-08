<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\Notifikasi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\Platform;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    private function pelangganAktif(): Pelanggan
    {
        $user = auth()->user();

        return Pelanggan::firstOrCreate(
            ['nama_pelanggan' => $user->name],
            ['jenis_kelamin' => 'Lainnya', 'kota' => 'Online']
        );
    }

    public function index()
    {
        $pelanggan = $this->pelangganAktif();

        $items = Keranjang::with('produk.varians')
            ->where('pelanggan_id', $pelanggan->id)
            ->latest()
            ->get();

        $grandTotalProduk = $items->sum(fn ($item) => $item->subtotal);
        $biayaPengiriman = $items->count() > 0 ? 10000 : 0;
        $ewalletFavorit = $pelanggan->ewallet_favorit;

        return view('customer.keranjang', compact('items', 'grandTotalProduk', 'biayaPengiriman', 'ewalletFavorit'));
    }

    // Tambah produk ke keranjang dari halaman katalog/promo.
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'ukuran'    => 'required|string',
            'warna'     => 'required|in:' . implode(',', Produk::WARNA_OPTIONS),
            'kuantitas' => 'required|integer|min:1',
        ]);

        $pelanggan = $this->pelangganAktif();
        $produk = Produk::findOrFail($request->produk_id);

        // Cek stok pada tabel produk_varians
        $varian = ProdukVarian::where('produk_id', $request->produk_id)
            ->where('ukuran', $request->ukuran)
            ->where('warna', $request->warna)
            ->first();

        $stokTersedia = $varian ? $varian->stok : 0;

        if (!$varian || $stokTersedia < $request->kuantitas) {
            return back()->withErrors([
                'kuantitas' => "Stok \"{$produk->nama_produk}\" ukuran {$request->ukuran} warna {$request->warna} tinggal {$stokTersedia}.",
            ]);
        }

        $item = Keranjang::firstOrNew([
            'pelanggan_id' => $pelanggan->id,
            'produk_id'    => $produk->id,
            'ukuran'       => $request->ukuran,
            'warna'        => $request->warna,
        ]);

        $item->kuantitas = ($item->exists ? $item->kuantitas : 0) + $request->kuantitas;
        $item->save();

        return back()->with('success', "\"{$produk->nama_produk}\" ({$request->ukuran} - {$request->warna}) ditambahkan ke keranjang.");
    }

    // Ubah kuantitas salah satu item di keranjang.
    public function update(Request $request, $id)
    {
        $request->validate([
            'kuantitas' => 'required|integer|min:1',
        ]);

        $pelanggan = $this->pelangganAktif();

        $item = Keranjang::with('produk')
            ->where('pelanggan_id', $pelanggan->id)
            ->findOrFail($id);

        // Cek stok varian
        $varian = ProdukVarian::where('produk_id', $item->produk_id)
            ->where('ukuran', $item->ukuran)
            ->where('warna', $item->warna)
            ->first();

        $stokTersedia = $varian ? $varian->stok : 0;

        if (!$varian || $stokTersedia < $request->kuantitas) {
            return back()->withErrors([
                'kuantitas' => "Stok \"{$item->produk->nama_produk}\" ({$item->ukuran}-{$item->warna}) tinggal {$stokTersedia}.",
            ]);
        }

        $item->update(['kuantitas' => $request->kuantitas]);

        return back()->with('success', 'Jumlah pesanan diperbarui.');
    }

    // Hapus satu item dari keranjang.
    public function destroy($id)
    {
        $pelanggan = $this->pelangganAktif();

        Keranjang::where('pelanggan_id', $pelanggan->id)->findOrFail($id)->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    // Checkout seluruh isi keranjang
    public function checkout(Request $request)
    {
        $request->validate([
            'item_ids'           => 'required|array|min:1',
            'item_ids.*'         => 'integer',
            'metode_pembayaran'  => 'required|in:Transfer Bank,E-Wallet',
            'ewallet_provider'   => 'required_if:metode_pembayaran,E-Wallet|nullable|in:' . implode(',', Pelanggan::EWALLET_OPTIONS),
        ], [
            'item_ids.required'            => 'Pilih dulu produk yang mau di-checkout.',
            'ewallet_provider.required_if'  => 'Pilih provider e-wallet-nya dulu ya.',
        ]);

        $pelanggan = $this->pelangganAktif();

        $items = Keranjang::with('produk')
            ->where('pelanggan_id', $pelanggan->id)
            ->whereIn('id', $request->item_ids)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('customer.keranjang.index')->withErrors([
                'keranjang' => 'Pilih dulu produk di keranjang yang mau di-checkout.',
            ]);
        }

        $platform = Platform::where('nama_platform', 'Website')->first();
        $platformId = $platform ? $platform->id : 1;

        $metodePembayaran = $request->metode_pembayaran === 'E-Wallet'
            ? 'E-Wallet - ' . $request->ewallet_provider
            : $request->metode_pembayaran;

        if ($request->metode_pembayaran === 'E-Wallet') {
            $pelanggan->update(['ewallet_favorit' => $request->ewallet_provider]);
        }

        $statusPembayaranAwal = Transaksi::STATUS_MENUNGGU_PEMBAYARAN;

        try {
            $transaksi = DB::transaction(function () use ($items, $pelanggan, $platformId, $metodePembayaran, $statusPembayaranAwal, $request) {

                $totalDiskon = 0;
                $subtotalSemua = 0;
                $rincianItem = [];

                // Kunci & validasi stok dari tabel produk_varians
                foreach ($items as $item) {
                    $produk = Produk::whereKey($item->produk_id)->firstOrFail();

                    $varian = ProdukVarian::where('produk_id', $item->produk_id)
                        ->where('ukuran', $item->ukuran)
                        ->where('warna', $item->warna)
                        ->lockForUpdate()
                        ->first();

                    $stokTersedia = $varian ? $varian->stok : 0;

                    if (!$varian || $stokTersedia < $item->kuantitas) {
                        throw new \RuntimeException("Stok \"{$produk->nama_produk}\" ukuran {$item->ukuran} warna {$item->warna} tinggal {$stokTersedia}.");
                    }

                    $hargaSatuanAwal = $produk->harga_satuan;
                    $diskonPerUnit = (int) round($hargaSatuanAwal * $produk->diskon_persen / 100);
                    $diskonBaris = $diskonPerUnit * $item->kuantitas;
                    $subtotalBaris = ($hargaSatuanAwal * $item->kuantitas) - $diskonBaris;

                    $totalDiskon += $diskonBaris;
                    $subtotalSemua += $subtotalBaris;

                    $rincianItem[] = [
                        'produk'         => $produk,
                        'varian'         => $varian,
                        'ukuran'         => $item->ukuran,
                        'warna'          => $item->warna,
                        'kuantitas'      => $item->kuantitas,
                        'harga_satuan'   => $hargaSatuanAwal,
                        'diskon_baris'   => $diskonBaris,
                        'subtotal_baris' => $subtotalBaris,
                    ];
                }

                $biayaPengiriman = 10000;
                $grandTotal = $subtotalSemua + $biayaPengiriman;

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
                    'total_diskon'       => $totalDiskon,
                    'grand_total'        => $grandTotal,
                ]);

                foreach ($rincianItem as $r) {
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id'    => $r['produk']->id,
                        'ukuran'       => $r['ukuran'],
                        'warna'        => $r['warna'],
                        'kuantitas'    => $r['kuantitas'],
                        'harga_satuan' => $r['harga_satuan'],
                        'diskon'       => $r['diskon_baris'],
                        'subtotal'     => $r['subtotal_baris'],
                    ]);

                    // Potong stok pada VARIAN spesifik
                    $r['varian']->decrement('stok', $r['kuantitas']);
                }

                // Cuma hapus item yang dicentang & di-checkout, sisanya tetap di keranjang.
                Keranjang::where('pelanggan_id', $pelanggan->id)->whereIn('id', $request->item_ids)->delete();

                return $transaksi;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('customer.keranjang.index')->withErrors([
                'kuantitas' => $e->getMessage(),
            ]);
        }

        Notifikasi::buatUntukAdmin(
            'pesanan_baru',
            'Pesanan Baru',
            "{$transaksi->kode_transaksi} dari {$pelanggan->nama_pelanggan} senilai Rp " . number_format($transaksi->grand_total, 0, ',', '.'),
            $transaksi->id
        );

        return redirect()->route('customer.pesanan.show', $transaksi->id)
            ->with('success', 'Pesanan dari keranjang berhasil dibuat! Silakan selesaikan pembayaran di bawah ini.');
    }
}