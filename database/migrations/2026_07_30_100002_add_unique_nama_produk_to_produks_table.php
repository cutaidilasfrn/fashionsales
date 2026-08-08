<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PENTING: kalau migration ini gagal saat dijalankan, artinya di tabel produks
     * kamu SUDAH ADA nama_produk yang sama persis (duplikat). Laravel/MySQL akan
     * menolak bikin index unique kalau datanya bentrok.
     *
     * Cara ceknya, jalankan query ini dulu di phpMyAdmin:
     *   SELECT nama_produk, COUNT(*) FROM produks GROUP BY nama_produk HAVING COUNT(*) > 1;
     * Lalu ganti nama / hapus salah satu produk yang duplikat sebelum migrate lagi.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->unique('nama_produk');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique(['nama_produk']);
        });
    }
};
