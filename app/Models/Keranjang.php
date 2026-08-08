<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keranjang extends Model
{
    protected $fillable = [
        'pelanggan_id',
        'produk_id',
        'ukuran',
        'warna',
        'kuantitas',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    // Harga satuan yang berlaku (sudah memperhitungkan diskon promo produk)
    public function getHargaSatuanAttribute(): int
    {
        return $this->produk->harga_promo;
    }

    // Subtotal baris ini = harga satuan (setelah promo) x kuantitas
    public function getSubtotalAttribute(): int
    {
        return $this->harga_satuan * $this->kuantitas;
    }
}
