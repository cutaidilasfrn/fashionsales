<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // Siapa yang membatalkan: 'customer', 'sistem' (auto-expire), atau 'admin'.
            // Dipakai untuk mengunci status_pesanan dari admin KECUALI admin sendiri
            // yang membatalkan (lihat Transaksi::statusPesananTerkunci()).
            if (!Schema::hasColumn('transaksis', 'dibatalkan_oleh')) {
                $table->string('dibatalkan_oleh')->nullable()->after('status_pesanan');
            }

            // Alasan pembatalan yang ditampilkan ke customer & admin.
            if (!Schema::hasColumn('transaksis', 'alasan_pembatalan')) {
                $table->text('alasan_pembatalan')->nullable()->after('dibatalkan_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['dibatalkan_oleh', 'alasan_pembatalan']);
        });
    }
};
