<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Stok barang yang tersedia
            $table->integer('stok')->default(0)->after('gambar');

            // Persentase diskon untuk fitur halaman Promo (0 - 100)
            $table->unsignedTinyInteger('diskon_persen')->default(0)->after('stok');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['stok', 'diskon_persen']);
        });
    }
};
