<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- 1. Tambahkan import ini di atas
use Illuminate\Support\Facades\View;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Transaksi;
use App\Models\Notifikasi;
use App\Models\Keranjang;
use App\Models\Pelanggan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // <--- 2. Tambahkan baris ini di dalam method boot()
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            if (!auth()->check()) {
                return;
            }

            // Sapu pesanan yang lewat batas waktu 1x24 jam tanpa pembayaran
            // terverifikasi, setiap kali ada yang buka halaman manapun.
            // Ini pengganti cron/scheduler server yang belum tentu tersedia
            // di lingkungan development lokal (php artisan serve).
            Transaksi::batalkanYangKadaluarsa();

            // Sapu juga pesanan yang sudah Dikirim tapi customer tidak kunjung
            // konfirmasi diterima — auto-selesaikan setelah beberapa hari.
            Transaksi::selesaikanYangKadaluarsa();

            if (in_array(auth()->user()->role, ['admin', 'kasir'])) {
                // Notifikasi stok menipis: dicek per KOMBINASI warna+ukuran
                // (bukan cuma total produk), supaya kelihatan persis ukuran/warna
                // mana yang mau habis. Dikelompokkan per produk biar rapi ditampilkan.
                $variasiMenipis = ProdukVarian::with('produk')
                    ->where('stok', '<=', Produk::BATAS_STOK_MENIPIS)
                    ->orderBy('stok')
                    ->get()
                    ->filter(fn ($v) => $v->produk !== null)
                    ->groupBy('produk_id')
                    ->sortBy(fn ($grup) => $grup->min('stok'));

                $view->with('stokMenipisList', $variasiMenipis);

                // Notifikasi pesanan baru (status masih Pending, belum diproses admin)
                $view->with(
                    'pesananBaruList',
                    Transaksi::where('status_pesanan', 'Pending')->latest()->take(10)->get()
                );

                // Notifikasi persisten: pesanan baru, bukti pembayaran diunggah,
                // pesanan dibatalkan customer/sistem.
                $view->with('notifikasiList', Notifikasi::untukAdmin()->latest()->take(10)->get());
                $view->with('notifikasiBelumDibacaCount', Notifikasi::untukAdmin()->belumDibaca()->count());
            }

            if (auth()->user()->role === 'customer') {
                // Jumlah item di keranjang, ditampilkan sebagai badge di menu sidebar
                $pelanggan = Pelanggan::where('nama_pelanggan', auth()->user()->name)->first();
                $view->with('jumlahKeranjang', $pelanggan ? Keranjang::where('pelanggan_id', $pelanggan->id)->sum('kuantitas') : 0);

                // Notifikasi persisten: pembayaran ditolak, pesanan diproses/dikirim/dibatalkan admin.
                if ($pelanggan) {
                    $view->with('notifikasiList', Notifikasi::untukPelanggan($pelanggan->id)->latest()->take(10)->get());
                    $view->with('notifikasiBelumDibacaCount', Notifikasi::untukPelanggan($pelanggan->id)->belumDibaca()->count());
                }
            }
        });
    }
}