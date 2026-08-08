<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Platform;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Stat Cards
        // Pendapatan HANYA dihitung dari transaksi yang sudah "Selesai".
        // Pending/Proses/Dikirim belum tentu jadi uang (masih bisa batal/retur),
        // jadi tidak boleh ikut menambah pendapatan — cukup dikecualikan dari
        // status "Batal" saja tidak cukup akurat.
        $totalPendapatan = Transaksi::where('status_pesanan', 'Selesai')->sum('grand_total');
        $totalTransaksi  = Transaksi::where('status_pesanan', '!=', 'Batal')->count();
        $totalProduk     = Produk::count();
        $totalPelanggan  = Pelanggan::count();

        // 2. Produk Terlaris (Top 5)
        // Hanya menghitung produk dari transaksi yang tidak dibatalkan
        $produkTerlaris = DB::table('detail_transaksis')
            ->join('produks','produks.id','=','detail_transaksis.produk_id')
            ->join('transaksis','transaksis.id','=','detail_transaksis.transaksi_id')
            ->where('transaksis.status_pesanan', '!=', 'Batal')
            ->select(
                'produks.nama_produk',
                DB::raw('SUM(detail_transaksis.kuantitas) as total')
            )
            ->groupBy('produks.id','produks.nama_produk')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 3. Platform Sales
        // Hanya menghitung transaksi yang tidak dibatalkan
        $platformTerlaris = Platform::leftJoin(
                'transaksis',
                function ($join) {
                    $join->on('platforms.id', '=', 'transaksis.platform_id')
                         ->where('transaksis.status_pesanan', '!=', 'Batal');
                }
            )
            ->select(
                'platforms.nama_platform',
                DB::raw('COUNT(transaksis.id) as total')
            )
            ->groupBy(
                'platforms.id',
                'platforms.nama_platform'
            )
            ->get();

        // 4. Deteksi Tahun Terbaru dari Data Transaksi
        $tahunTerbaru = Transaksi::selectRaw("strftime('%Y', tanggal_transaksi) as tahun")
            ->orderByDesc('tanggal_transaksi')
            ->value('tahun') ?? date('Y');

        // 5. Query Pendapatan Bulanan berdasarkan Tahun Tersebut
        // Hanya transaksi berstatus "Selesai" yang dihitung sebagai pendapatan
        $penjualanBulanan = Transaksi::select(
                DB::raw("CAST(strftime('%m', tanggal_transaksi) AS INTEGER) as bulan"),
                DB::raw('SUM(grand_total) as total')
            )
            ->whereYear('tanggal_transaksi', $tahunTerbaru)
            ->where('status_pesanan', 'Selesai')
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC')
            ->pluck('total', 'bulan')
            ->toArray();

        // Susun array 12 bulan
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartData   = [];

        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $penjualanBulanan[$m] ?? 0;
        }

        return view('transaksi.dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'totalProduk',
            'totalPelanggan',
            'produkTerlaris',
            'platformTerlaris',
            'chartLabels',
            'chartData',
            'tahunTerbaru' // Dikirim ke view agar judul grafik dinamis sesuai tahun data
        ));
    }
}