<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {
            // Warna yang dipilih customer saat pesan. Nullable supaya data
            // transaksi lama (sebelum fitur warna ada) tetap valid.
            $table->string('warna')->nullable()->after('ukuran');
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->dropColumn('warna');
        });
    }
};
