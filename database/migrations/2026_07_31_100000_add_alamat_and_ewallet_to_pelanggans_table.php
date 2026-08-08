<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            // Alamat lengkap pelanggan, dipakai untuk transaksi & profil
            $table->text('alamat')->nullable()->after('kota');

            // E-wallet favorit pelanggan (opsional), biar tiap checkout
            // ga perlu isi ulang provider e-wallet-nya dari awal
            $table->string('ewallet_favorit')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'ewallet_favorit']);
        });
    }
};
