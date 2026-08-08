<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'status_pembayaran')) {
                $table->string('status_pembayaran')->default('Menunggu Pembayaran')->after('metode_pembayaran');
            }
            if (!Schema::hasColumn('transaksis', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('status_pembayaran');
            }
            if (!Schema::hasColumn('transaksis', 'catatan_pembayaran')) {
                $table->string('catatan_pembayaran')->nullable()->after('bukti_pembayaran');
            }
        });

        DB::table('transaksis')->update(['status_pembayaran' => 'Lunas']);
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'bukti_pembayaran', 'catatan_pembayaran']);
        });
    }
};