<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $fillable = [
        'target',
        'pelanggan_id',
        'transaksi_id',
        'tipe',
        'judul',
        'pesan',
        'dibaca_at',
    ];

    protected $casts = [
        'dibaca_at' => 'datetime',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function scopeUntukAdmin($query)
    {
        return $query->where('target', 'admin');
    }

    public function scopeUntukPelanggan($query, int $pelangganId)
    {
        return $query->where('target', 'customer')->where('pelanggan_id', $pelangganId);
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_at');
    }

    /**
     * Buat notifikasi untuk semua admin. Dipanggil setiap ada pesanan baru,
     * bukti pembayaran diunggah, atau pesanan dibatalkan customer/sistem.
     */
    public static function buatUntukAdmin(string $tipe, string $judul, string $pesan, ?int $transaksiId = null): self
    {
        return self::create([
            'target'       => 'admin',
            'transaksi_id' => $transaksiId,
            'tipe'         => $tipe,
            'judul'        => $judul,
            'pesan'        => $pesan,
        ]);
    }

    /**
     * Buat notifikasi untuk 1 pelanggan tertentu. Dipanggil setiap admin
     * menolak pembayaran, atau mengubah status pesanan jadi Proses/Dikirim/Batal.
     */
    public static function buatUntukPelanggan(int $pelangganId, string $tipe, string $judul, string $pesan, ?int $transaksiId = null): self
    {
        return self::create([
            'target'       => 'customer',
            'pelanggan_id' => $pelangganId,
            'transaksi_id' => $transaksiId,
            'tipe'         => $tipe,
            'judul'        => $judul,
            'pesan'        => $pesan,
        ]);
    }

    // Ikon Bootstrap Icons per tipe, dipakai di dropdown notifikasi.
    public function iconClass(): string
    {
        return match ($this->tipe) {
            'pesanan_baru'                  => 'bi-bag-plus-fill text-primary',
            'pembayaran_diunggah'           => 'bi-upload text-info',
            'pesanan_dibatalkan_customer'   => 'bi-x-circle-fill text-danger',
            'pesanan_dibatalkan_sistem'     => 'bi-clock-history text-danger',
            'pesanan_dibatalkan_admin'      => 'bi-x-circle-fill text-danger',
            'pembayaran_ditolak'            => 'bi-x-octagon-fill text-danger',
            'pesanan_diproses'              => 'bi-gear-fill text-primary',
            'pesanan_dikirim'               => 'bi-truck text-info',
            'pesanan_selesai_otomatis'      => 'bi-check-circle-fill text-success',
            'pesanan_selesai_customer'      => 'bi-check-circle-fill text-success',
            default                         => 'bi-bell-fill text-secondary',
        };
    }
}
