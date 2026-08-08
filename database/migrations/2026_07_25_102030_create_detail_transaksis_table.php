<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('detail_transaksis', function (Blueprint $table) {

    $table->id();

    $table->foreignId('transaksi_id')
          ->constrained('transaksis')
          ->cascadeOnDelete();

    $table->foreignId('produk_id')
          ->constrained('produks')
          ->cascadeOnDelete();

    $table->string('ukuran');

    $table->integer('kuantitas');

    $table->integer('harga_satuan');

    $table->integer('diskon');

    $table->integer('subtotal');

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};
