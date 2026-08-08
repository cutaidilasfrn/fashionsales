<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjangs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pelanggan_id')
                  ->constrained('pelanggans')
                  ->cascadeOnDelete();

            $table->foreignId('produk_id')
                  ->constrained('produks')
                  ->cascadeOnDelete();

            $table->string('ukuran');
            $table->string('warna');
            $table->integer('kuantitas');

            $table->timestamps();

            // Kombinasi produk+ukuran+warna yang sama untuk 1 pelanggan tidak boleh
            // dobel baris. Kalau ditambahkan lagi, kuantitasnya yang ditambah
            // (lihat CartController@store).
            $table->unique(['pelanggan_id', 'produk_id', 'ukuran', 'warna'], 'keranjang_unik_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjangs');
    }
};
