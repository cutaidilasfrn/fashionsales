<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    /**
     * Admin hanya boleh MELIHAT data pelanggan (read-only), bukan
     * menambah/mengubah/menghapus. Data pelanggan adalah hak milik
     * pelanggan itu sendiri, diisi lewat registrasi & halaman profil.
     */
    public function index(Request $request)
    {
        $query = Pelanggan::leftJoin(
                'transaksis',
                'pelanggans.id',
                '=',
                'transaksis.pelanggan_id'
            )
            ->select(
                'pelanggans.*',
                DB::raw('COUNT(transaksis.id) as total_transaksi')
            );

        if ($request->filled('q')) {

            $search = $request->q;

            $query->where(function ($q) use ($search) {

                $q->where('pelanggans.nama_pelanggan', 'like', "%{$search}%")
                  ->orWhere('pelanggans.kota', 'like', "%{$search}%")
                  ->orWhere('pelanggans.jenis_kelamin', 'like', "%{$search}%");

            });

        }

        $pelanggans = $query
            ->groupBy(
                'pelanggans.id',
                'pelanggans.nama_pelanggan',
                'pelanggans.jenis_kelamin',
                'pelanggans.kota',
                'pelanggans.alamat',
                'pelanggans.ewallet_favorit',
                'pelanggans.created_at',
                'pelanggans.updated_at'
            )
            ->orderBy('pelanggans.nama_pelanggan')
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.pelanggan', compact('pelanggans'));
    }
}
