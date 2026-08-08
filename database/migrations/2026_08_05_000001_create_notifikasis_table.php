<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();

            // 'admin' atau 'customer' — dipakai scopeUntukAdmin() / scopeUntukPelanggan()
            $table->string('target');

            // Hanya diisi kalau target = 'customer'. Nullable karena notifikasi
            // untuk admin tidak terikat ke satu pelanggan tertentu.
            $table->foreignId('pelanggan_id')
                  ->nullable()
                  ->constrained('pelanggans')
                  ->cascadeOnDelete();

            // Nullable juga, jaga-jaga kalau nanti ada notifikasi yang tidak
            // terkait transaksi tertentu.
            $table->foreignId('transaksi_id')
                  ->nullable()
                  ->constrained('transaksis')
                  ->cascadeOnDelete();

            $table->string('tipe');
            $table->string('judul');
            $table->text('pesan');

            // NULL = belum dibaca. Diisi timestamp begitu dibaca (dipakai scopeBelumDibaca()).
            $table->timestamp('dibaca_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};