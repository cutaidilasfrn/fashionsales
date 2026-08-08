<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->string('ukuran'); // S, M, L, XL
            $table->string('warna');  // Hitam, Putih, Navy
            $table->integer('stok')->default(0);
            $table->string('gambar')->nullable(); // Gambar khusus warna ini
            $table->timestamps();

            // Kombinasi produk + ukuran + warna tidak boleh dobel
            $table->unique(['produk_id', 'ukuran', 'warna'], 'produk_varian_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_varians');
    }
};