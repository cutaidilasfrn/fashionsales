<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Simpan "snapshot" data pelanggan (nama, jenis kelamin, kota, alamat)
     * langsung di tabel transaksis, persis seperti detail_transaksis yang
     * sudah menyimpan snapshot harga_satuan produk.
     *
     * Alasan: pelanggan bisa mengubah datanya sendiri lewat halaman profil
     * (jenis_kelamin, kota, alamat, ewallet_favorit) kapan saja. Kalau detail
     * transaksi lama hanya join ke tabel pelanggans (data terkini), maka
     * histori transaksi lama akan ikut berubah setiap kali pelanggan edit
     * profil — padahal transaksi itu dibuat dengan data yang berlaku SAAT
     * transaksi dilakukan. Snapshot ini membuat histori transaksi tetap
     * merepresentasikan kondisi data pada saat transaksi terjadi.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'nama_pelanggan')) {
                $table->string('nama_pelanggan')->nullable()->after('pelanggan_id');
            }
            if (!Schema::hasColumn('transaksis', 'jenis_kelamin')) {
                $table->string('jenis_kelamin')->nullable()->after('nama_pelanggan');
            }
            if (!Schema::hasColumn('transaksis', 'kota')) {
                $table->string('kota')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('transaksis', 'alamat')) {
                $table->text('alamat')->nullable()->after('kota');
            }
        });

        // Backfill transaksi lama dengan data pelanggan yang ada SEKARANG.
        // Ini best-effort karena histori perubahan data pelanggan sebelum
        // migration ini tidak pernah disimpan, sehingga tidak bisa
        // direkonstruksi secara akurat. Transaksi baru setelah migration ini
        // akan selalu diisi otomatis saat checkout (lihat CustomerController).
        DB::table('transaksis')->orderBy('id')->chunkById(200, function ($transaksis) {
            foreach ($transaksis as $t) {
                $p = DB::table('pelanggans')->where('id', $t->pelanggan_id)->first();

                if ($p) {
                    DB::table('transaksis')->where('id', $t->id)->update([
                        'nama_pelanggan' => $p->nama_pelanggan,
                        'jenis_kelamin'  => $p->jenis_kelamin,
                        'kota'           => $p->kota,
                        'alamat'         => $p->alamat ?? null,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['nama_pelanggan', 'jenis_kelamin', 'kota', 'alamat']);
        });
    }
};