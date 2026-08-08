<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_produk',
        'harga_satuan',
        'material',
        'deskripsi',
        'gambar',
        'diskon_persen',
    ];

    // Palet warna yang tersedia untuk dipilih admin. Tiap produk boleh pilih
    // beberapa warna dari daftar ini (lihat MAX_WARNA_PER_PRODUK) — TIDAK harus
    // semua warna dipakai di semua produk.
    const WARNA_OPTIONS = [
        'Hitam',
        'Putih',
        'Abu-abu',
        'Merah',
        'Biru',
        'Navy',
        'Cokelat',
        'Krem',
    ];

    const UKURAN_OPTIONS = ['S', 'M', 'L', 'XL'];

    // Maksimal jumlah warna per produk, supaya admin tidak kewalahan mengurus
    // gambar & stok per warna untuk banyak produk sekaligus.
    const MAX_WARNA_PER_PRODUK = 4;

    const BATAS_STOK_MENIPIS = 5;

    public function varians(): HasMany
    {
        return $this->hasMany(ProdukVarian::class);
    }

    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function keranjangs(): HasMany
    {
        return $this->hasMany(Keranjang::class);
    }

    // Hitung total stok dari seluruh varian milik produk ini
    public function getTotalStokAttribute(): int
    {
        return $this->varians->sum('stok');
    }

    // Daftar warna yang benar-benar dipilih/tersedia untuk produk ini
    // (bukan semua WARNA_OPTIONS global).
    public function warnaTersedia()
    {
        return $this->varians->pluck('warna')->unique()->values();
    }

    // Stok untuk 1 kombinasi warna + ukuran tertentu.
    public function stokUntuk(string $warna, string $ukuran): int
    {
        return (int) ($this->varians->first(
            fn ($v) => $v->warna === $warna && $v->ukuran === $ukuran
        )->stok ?? 0);
    }

    // Gambar untuk warna tertentu (fallback ke gambar cover kalau warna ini
    // tidak punya gambar sendiri).
    public function gambarUntukWarna(string $warna): ?string
    {
        $varian = $this->varians->first(fn ($v) => $v->warna === $warna && $v->gambar);
        return $varian->gambar ?? $this->gambar;
    }

    // Scope untuk mencari produk yang stok variannya menipis (<= 5)
    public function scopeStokMenipis($query)
    {
        return $query->whereHas('varians', function ($q) {
            $q->where('stok', '<=', self::BATAS_STOK_MENIPIS);
        });
    }

    public function scopePromo($query)
    {
        return $query->where('diskon_persen', '>', 0);
    }

    public function getHargaPromoAttribute(): int
    {
        if ($this->diskon_persen <= 0) {
            return $this->harga_satuan;
        }

        return (int) round($this->harga_satuan * (100 - $this->diskon_persen) / 100);
    }
}