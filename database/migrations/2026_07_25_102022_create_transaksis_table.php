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
    Schema::create('transaksis', function (Blueprint $table) {

    $table->id();

    $table->string('kode_transaksi')->unique();

    $table->foreignId('pelanggan_id')
          ->constrained('pelanggans')
          ->cascadeOnDelete();

    $table->foreignId('platform_id')
          ->constrained('platforms')
          ->cascadeOnDelete();

    $table->dateTime('tanggal_transaksi');

    $table->string('metode_pembayaran');

    $table->string('status_pesanan');

    $table->integer('biaya_pengiriman')->default(0);

    $table->integer('total_diskon')->default(0);

    $table->integer('grand_total')->default(0);

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
