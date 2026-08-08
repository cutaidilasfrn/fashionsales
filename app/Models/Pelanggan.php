<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $fillable = [
        'nama_pelanggan',
        'jenis_kelamin',
        'kota',
        'alamat',
        'ewallet_favorit',
    ];

    // Daftar provider e-wallet yang didukung sistem.
    // Dipakai di form checkout & profil customer.
    const EWALLET_OPTIONS = ['OVO', 'GoPay', 'DANA', 'ShopeePay', 'LinkAja'];

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function keranjangs(): HasMany
    {
        return $this->hasMany(Keranjang::class);
    }
}