<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;


class Transaksi extends Model
{
    protected $fillable = [
        'kode_transaksi',
        'pelanggan_id',
        'nama_pelanggan',
        'jenis_kelamin',
        'kota',
        'alamat',
        'platform_id',
        'tanggal_transaksi',
        'metode_pembayaran',
        'status_pesanan',
        'dibatalkan_oleh',
        'alasan_pembatalan',
        'status_pembayaran',
        'bukti_pembayaran',
        'catatan_pembayaran',
        'biaya_pengiriman',
        'total_diskon',
        'grand_total'
    ];

    // Semua nilai status_pembayaran yang valid, dipakai di validasi & Blade.
    const STATUS_MENUNGGU_PEMBAYARAN = 'Menunggu Pembayaran';
    const STATUS_MENUNGGU_VERIFIKASI = 'Menunggu Verifikasi';
    const STATUS_LUNAS = 'Lunas';
    const STATUS_DITOLAK = 'Ditolak';
    const STATUS_BELUM_DIBAYAR_COD = 'Belum Dibayar (COD)';

    const STATUS_PEMBAYARAN_OPTIONS = [
        self::STATUS_MENUNGGU_PEMBAYARAN,
        self::STATUS_MENUNGGU_VERIFIKASI,
        self::STATUS_LUNAS,
        self::STATUS_DITOLAK,
        self::STATUS_BELUM_DIBAYAR_COD,
    ];

    // Kelas badge Bootstrap per status, dipakai di semua view biar warnanya konsisten.
    public function badgePembayaranClass(): string
    {
        return match ($this->status_pembayaran) {
            self::STATUS_LUNAS => 'bg-success',
            self::STATUS_MENUNGGU_VERIFIKASI => 'bg-info text-dark',
            self::STATUS_DITOLAK => 'bg-danger',
            self::STATUS_BELUM_DIBAYAR_COD => 'bg-secondary',
            default => 'bg-warning text-dark', // Menunggu Pembayaran
        };
    }

    /**
     * Batas waktu pembayaran (1x24 jam sejak pesanan dibuat). Dipakai untuk
     * menampilkan countdown di halaman detail pesanan customer. Balikin null
     * kalau pesanan sudah tidak lagi menunggu pembayaran (sudah Lunas/Batal/dsb),
     * karena countdown-nya sudah tidak relevan lagi.
     */
    public function batasWaktuBayar(): ?\Carbon\Carbon
    {
        if ($this->status_pesanan !== 'Pending'
            || !in_array($this->status_pembayaran, [self::STATUS_MENUNGGU_PEMBAYARAN, self::STATUS_DITOLAK], true)) {
            return null;
        }

        return $this->created_at->copy()->addHours(24);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function detailTransaksis(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    /**
     * Batalkan transaksi ini & kembalikan stok tiap varian yang dipesan.
     * Dipakai di 3 tempat: customer batalkan sendiri, admin batalkan manual,
     * dan auto-batal karena lewat batas waktu pembayaran.
     *
     * Aman dipanggil berkali-kali — kalau sudah Batal sebelumnya, stok
     * TIDAK dikembalikan lagi (supaya tidak dobel nambah stok).
     *
     * @param string $dibatalkanOleh 'customer', 'sistem', atau 'admin' — dipakai
     *                                statusPesananTerkunci() untuk menentukan apakah
     *                                admin masih boleh mengubah status pesanan ini.
     * @param string|null $alasan Alasan pembatalan, ditampilkan ke customer & admin.
     */
    public function batalkanDanKembalikanStok(string $dibatalkanOleh = 'sistem', ?string $alasan = null): void
    {
        if ($this->status_pesanan === 'Batal') {
            return;
        }

        DB::transaction(function () use ($dibatalkanOleh, $alasan) {
            foreach ($this->detailTransaksis as $detail) {
                ProdukVarian::where('produk_id', $detail->produk_id)
                ->where('ukuran', $detail->ukuran)
                ->where('warna', $detail->warna)
                ->increment('stok', $detail->kuantitas);
            }

            $this->update([
                'status_pesanan'    => 'Batal',
                'dibatalkan_oleh'   => $dibatalkanOleh,
                'alasan_pembatalan' => $alasan,
            ]);
        });
    }

    /**
     * Kalau pesanan sudah Batal karena dibatalkan customer sendiri atau otomatis
     * oleh sistem (kadaluarsa), admin TIDAK boleh lagi mengubah status pesanan
     * (Pending/Proses/Dikirim/Selesai) — pesanan ini final, cuma bisa dilihat.
     * Kalau admin sendiri yang membatalkan, admin tetap boleh mengubahnya lagi
     * (misal salah pencet dan mau dikembalikan ke status semula).
     */
    public function statusPesananTerkunci(): bool
    {
        return $this->status_pesanan === 'Batal' && $this->dibatalkan_oleh !== 'admin';
    }

    /**
     * Customer cuma boleh batalkan sendiri selama pesanan masih Pending DAN
     * pembayarannya belum Lunas. Begitu admin konfirmasi Lunas (barang mulai
     * disiapkan), pembatalan mandiri ditutup — harus hubungi admin.
     */
    public function bolehDibatalkanCustomer(): bool
    {
        return $this->status_pesanan === 'Pending'
            && $this->status_pembayaran !== self::STATUS_LUNAS;
    }

    /**
     * Tombol "Pesanan Diterima" cuma muncul kalau admin sudah menandai
     * pesanan sebagai Dikirim. Customer sendiri yang memicu jadi Selesai,
     * karena aplikasi ini tidak terhubung ke API tracking kurir mana pun.
     */
    public function bolehKonfirmasiDiterima(): bool
    {
        return $this->status_pesanan === 'Dikirim';
    }

    /**
     * Sapu semua pesanan yang sudah berstatus Dikirim selama lebih dari 7 hari
     * tapi customer tidak kunjung menekan "Pesanan Diterima". Ditandai Selesai
     * otomatis, supaya pesanan tidak nyangkut selamanya kalau customer lupa
     * konfirmasi — anggapannya barang pasti sudah sampai dalam rentang waktu itu.
     *
     * Dipanggil dari 2 tempat: AppServiceProvider (jalan tiap ada yang buka
     * halaman) dan dari scheduled command di routes/console.php.
     */
    public static function selesaikanYangKadaluarsa(): void
    {
        $kadaluarsa = self::where('status_pesanan', 'Dikirim')
            ->where('updated_at', '<=', now()->subDays(7))
            ->get();

        foreach ($kadaluarsa as $transaksi) {
            $transaksi->update(['status_pesanan' => 'Selesai']);

            if ($transaksi->pelanggan_id) {
                Notifikasi::buatUntukPelanggan(
                    $transaksi->pelanggan_id,
                    'pesanan_selesai_otomatis',
                    'Pesanan Ditandai Selesai',
                    "{$transaksi->kode_transaksi} otomatis ditandai Selesai karena sudah lebih dari 7 hari sejak dikirim tanpa konfirmasi. Hubungi admin kalau barang belum kamu terima.",
                    $transaksi->id
                );
            }
        }
    }

    /**
     * Sapu semua pesanan yang lewat batas waktu 1x24 jam TANPA pembayaran
     * berhasil diverifikasi, lalu batalkan otomatis + kembalikan stoknya.
     *
     * Sengaja HANYA menyasar status_pembayaran Menunggu Pembayaran / Ditolak.
     * Pesanan yang sudah "Menunggu Verifikasi" (customer sudah upload bukti,
     * tinggal nunggu admin cek) TIDAK ikut dibatalkan otomatis — customer
     * sudah menepati kewajibannya, jangan sampai dirugikan karena admin
     * telat verifikasi.
     *
     * Dipanggil dari 2 tempat: AppServiceProvider (jalan tiap ada yang buka
     * halaman, supaya tetap berfungsi tanpa cron server) dan dari scheduled
     * command di routes/console.php (cara yang benar kalau nanti di-deploy
     * ke server sungguhan dengan cron aktif).
     */
    public static function batalkanYangKadaluarsa(): void
    {
        $kadaluarsa = self::with('detailTransaksis')
            ->where('status_pesanan', 'Pending')
            ->whereIn('status_pembayaran', [self::STATUS_MENUNGGU_PEMBAYARAN, self::STATUS_DITOLAK])
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        foreach ($kadaluarsa as $transaksi) {
            $transaksi->batalkanDanKembalikanStok(
                'sistem',
                'Pesanan otomatis dibatalkan karena tidak dibayar dalam 1x24 jam sejak pesanan dibuat.'
            );

            Notifikasi::buatUntukAdmin(
                'pesanan_dibatalkan_sistem',
                'Pesanan Kadaluarsa Dibatalkan',
                "{$transaksi->kode_transaksi} ({$transaksi->nama_pelanggan}) otomatis dibatalkan karena tidak dibayar dalam 1x24 jam.",
                $transaksi->id
            );
        }
    }

    /**
     * Buat kode transaksi berikutnya secara berurutan, misal TRX000501
     * jika kode terakhir adalah TRX000500.
     *
     * Hanya kode dengan format TRX diikuti angka murni yang dihitung,
     * supaya kode lama (mis. sisa uniqid seperti TRX6A6DB120BA7F9) tidak
     * ikut mempengaruhi perhitungan nomor berikutnya.
     *
     * Method ini menggunakan lockForUpdate() dan HARUS dipanggil di dalam
     * DB::transaction() supaya aman dari race condition saat ada dua
     * transaksi masuk bersamaan.
     */
    public static function generateKodeTransaksi(string $prefix = 'TRX', int $panjangDigit = 6): string
    {
        $panjangPrefix = strlen($prefix);

        // Ambil kode transaksi terakhir yang formatnya "rapi" (mis. TRX000501),
        // dicek dari panjang totalnya, supaya kode lama yang formatnya beda
        // (mis. sisa uniqid seperti TRX6A6DB120BA7F9) tidak ikut kehitung.
        $kodeTerakhir = self::where('kode_transaksi', 'LIKE', $prefix . '%')
            ->whereRaw('LENGTH(kode_transaksi) = ?', [$panjangPrefix + $panjangDigit])
            ->orderByDesc('kode_transaksi')
            ->lockForUpdate()
            ->value('kode_transaksi');

        $nomorBerikutnya = $kodeTerakhir
            ? ((int) substr($kodeTerakhir, $panjangPrefix)) + 1
            : 1;

        return $prefix . str_pad((string) $nomorBerikutnya, $panjangDigit, '0', STR_PAD_LEFT);
    }
}