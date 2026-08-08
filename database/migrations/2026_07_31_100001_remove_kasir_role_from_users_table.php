<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PENTING - baca ini dulu sebelum migrate:
 *
 * Role 'kasir' dulu pernah ditambahkan lalu dihapus, tapi migration
 * yang nambahin 'kasir' ke enum `role` sudah kadung dijalankan &
 * datanya sudah kepakai di database kamu (ada akun-akun dengan role
 * 'kasir' beneran). File migration lamanya sudah kehapus, jadi kita
 * bersihkan lewat migration baru ini:
 *
 * 1. Semua user yang role-nya masih 'kasir' otomatis dipindah jadi 'admin'
 *    (karena dulunya kasir juga butuh akses kelola transaksi/produk mirip admin).
 *    SILAKAN CEK ULANG akun-akun ini setelah migrate, sesuaikan manual kalau
 *    ternyata kamu maunya mereka jadi 'customer' biasa, bukan admin:
 *      UPDATE users SET role = 'customer' WHERE email IN ('kasir@gmail.com', 'pija@gmail.com');
 *
 * 2. Baru setelah datanya aman, kolom enum `role` diperkecil lagi jadi
 *    cuma 'admin' dan 'customer'.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'kasir')->update(['role' => 'admin']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','customer') NOT NULL DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','kasir','customer') NOT NULL DEFAULT 'customer'");
    }
};
