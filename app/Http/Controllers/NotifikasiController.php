<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pelanggan;

class NotifikasiController extends Controller
{
    // Tandai semua notifikasi milik user yang sedang login sebagai sudah dibaca.
    // Satu route dipakai berdua oleh admin & customer, dibedakan dari role.
    public function bacaSemua()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            Notifikasi::untukAdmin()->belumDibaca()->update(['dibaca_at' => now()]);
        } else {
            $pelanggan = Pelanggan::where('nama_pelanggan', $user->name)->first();
            if ($pelanggan) {
                Notifikasi::untukPelanggan($pelanggan->id)->belumDibaca()->update(['dibaca_at' => now()]);
            }
        }

        return back();
    }
}
