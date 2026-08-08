<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\NotifikasiController;

// 1. Route Root (/) - Pengalihan Pintar Berdasarkan Role
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('dashboard'),
            default => redirect()->route('customer.katalog'),
        };
    }
    return redirect()->route('login');
});

// 2. Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Customer Routes
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer/dashboard', [CustomerController::class, 'katalog'])->name('customer.dashboard');
    Route::get('/katalog', [CustomerController::class, 'katalog'])->name('customer.katalog');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
    Route::get('/pesanan-saya', [CustomerController::class, 'pesananIndex'])->name('customer.pesanan.index');
    Route::get('/pesanan-saya/{id}', [CustomerController::class, 'pesananShow'])->name('customer.pesanan.show');
    Route::post('/pesanan-saya/{id}/bukti-pembayaran', [CustomerController::class, 'uploadBuktiPembayaran'])->name('customer.pesanan.uploadBukti');
    Route::post('/pesanan-saya/{id}/batalkan', [CustomerController::class, 'batalkanPesanan'])->name('customer.pesanan.batalkan');
    Route::post('/pesanan-saya/{id}/konfirmasi-diterima', [CustomerController::class, 'konfirmasiDiterima'])->name('customer.pesanan.konfirmasiDiterima');

    // Promo
    Route::get('/promo', [CustomerController::class, 'promo'])->name('customer.promo');

    // Keranjang
    Route::get('/keranjang', [CartController::class, 'index'])->name('customer.keranjang.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('customer.keranjang.store');
    Route::patch('/keranjang/{id}', [CartController::class, 'update'])->name('customer.keranjang.update');
    Route::delete('/keranjang/{id}', [CartController::class, 'destroy'])->name('customer.keranjang.destroy');
    Route::post('/keranjang/checkout', [CartController::class, 'checkout'])->name('customer.keranjang.checkout');

    // Profil Saya (data pelanggan + e-wallet favorit, hak customer sendiri)
    Route::get('/profil', [CustomerController::class, 'editProfil'])->name('customer.profil.edit');
    Route::put('/profil', [CustomerController::class, 'updateProfil'])->name('customer.profil.update');
});

// 3.5 Notifikasi (dipakai bareng admin & customer, cukup login apa saja)
Route::middleware('auth')->group(function () {
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
});

// 4. Admin Routes (Semua Pengelolaan & Transaksi Ada di Sini)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transaksi untuk Admin
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
    Route::patch('/transaksi/{id}/status', [TransaksiController::class, 'updateStatus'])->name('transaksi.updateStatus');
    Route::patch('/transaksi/{id}/pembayaran', [TransaksiController::class, 'updateStatusPembayaran'])->name('transaksi.updateStatusPembayaran');

    // CRUD Produk & Tambah Stok
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
    Route::post('/produk/{produk}/tambah-stok', [ProdukController::class, 'tambahStok'])->name('produk.tambah-stok');
    Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');

    // Data Pelanggan (read-only, admin tidak boleh CRUD data pelanggan)
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
});