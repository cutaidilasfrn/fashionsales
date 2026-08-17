# Fashion Sales

Aplikasi web manajemen penjualan produk fashion berbasis **Laravel 13**, dengan dua peran pengguna: **Admin** (kelola produk, transaksi, dan pelanggan) dan **Customer** (belanja, keranjang, dan pesanan). Dibangun dengan Blade + Bootstrap Icons di sisi tampilan, dan SQLite sebagai database default.

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Struktur Data Utama](#struktur-data-utama)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun & Role](#akun--role)
- [Konfigurasi Pembayaran](#konfigurasi-pembayaran)
- [Import Data Contoh (CSV)](#import-data-contoh-csv)
- [Struktur Direktori](#struktur-direktori)
- [Routing Ringkas](#routing-ringkas)
- [Testing](#testing)
- [Lisensi](#lisensi)

## Fitur

### Untuk Admin
- **Dashboard** — ringkasan pendapatan (dari pesanan berstatus *Selesai*), total transaksi, total produk, total pelanggan, produk terlaris, dan performa penjualan per platform.
- **Manajemen Produk** — CRUD produk dengan varian ukuran (S/M/L/XL) dan warna (maks. 4 warna per produk), stok per kombinasi ukuran+warna, gambar per varian, diskon persen, dan deteksi stok menipis.
- **Manajemen Transaksi** — lihat daftar & detail pesanan, ubah status pesanan (Pending → Proses → Dikirim → Selesai / Batal), verifikasi atau tolak bukti pembayaran.
- **Data Pelanggan** — tampilan read-only data pelanggan (admin tidak bisa mengubah data pribadi pelanggan).
- **Notifikasi** — bel notifikasi untuk pesanan baru, upload bukti pembayaran, dan pembatalan.

### Untuk Customer
- **Katalog Produk** — jelajahi produk berikut varian warna/ukuran dan harga promo.
- **Keranjang Belanja** — tambah, ubah kuantitas, hapus item, lalu checkout.
- **Checkout & Pembayaran** — pilih metode pembayaran (Transfer Bank / E-Wallet / COD), unggah bukti pembayaran.
- **Pesanan Saya** — lihat riwayat & status pesanan, batalkan pesanan (selama masih memenuhi syarat), konfirmasi barang diterima.
- **Halaman Promo** — daftar produk yang sedang diskon.
- **Profil** — kelola data diri (alamat, e-wallet favorit, dll).
- **Notifikasi** — pemberitahuan perubahan status pesanan.

### Logika Bisnis Otomatis
- Pesanan yang tidak dibayar dalam **1×24 jam** otomatis dibatalkan dan stok varian dikembalikan.
- Pesanan berstatus *Dikirim* yang tidak dikonfirmasi customer selama **7 hari** otomatis ditandai *Selesai*.
- Kedua proses di atas berjalan otomatis setiap ada request masuk (via `AppServiceProvider`) maupun melalui scheduled command (`routes/console.php`) jika di-deploy dengan cron aktif.

## Teknologi

| Layer      | Teknologi |
|------------|-----------|
| Backend    | PHP 8.3, Laravel 13 |
| Frontend   | Blade, Bootstrap Icons, Tailwind CSS 4 (via Vite) |
| Build tool | Vite 8, `laravel-vite-plugin` |
| Database   | SQLite (default), kompatibel dengan MySQL/PostgreSQL |
| Testing    | PHPUnit 12 |
| Lainnya    | Laravel Pint (code style), Laravel Pail (log viewer), Laravel Tinker |

## Struktur Data Utama

- **User** — akun login dengan `role` (`admin` atau `customer`).
- **Pelanggan** — data profil pembeli (nama, jenis kelamin, kota, alamat, e-wallet favorit).
- **Produk** — data produk (nama, harga, material, deskripsi, gambar, diskon).
- **ProdukVarian** — kombinasi ukuran + warna per produk beserta stok dan gambar variannya.
- **Platform** — kanal penjualan (mis. toko sendiri, marketplace, dsb).
- **Transaksi** — pesanan/order, menyimpan snapshot data pelanggan, status pesanan, dan status pembayaran.
- **DetailTransaksi** — item per baris dalam sebuah transaksi (produk, ukuran, warna, kuantitas, subtotal).
- **Keranjang** — isi keranjang belanja customer sebelum checkout.
- **Notifikasi** — notifikasi untuk admin dan/atau customer tertentu.

## Persyaratan

- PHP >= 8.3 beserta ekstensi umum Laravel (`pdo_sqlite`, `mbstring`, `fileinfo`, dll.)
- Composer 2
- Node.js >= 18 dan npm
- Git

## Instalasi

1. **Clone repository**
   ```bash
   git clone <url-repository-ini>
   cd fashion-sales
   ```

2. **Install dependency PHP**
   ```bash
   composer install
   ```

3. **Salin file environment**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Siapkan database SQLite** (default project ini menggunakan SQLite)
   ```bash
   touch database/database.sqlite
   ```
   Pastikan `.env` memuat konfigurasi berikut (default sudah sesuai):
   ```env
   DB_CONNECTION=sqlite
   ```
   > Jika ingin memakai MySQL/PostgreSQL, ubah `DB_CONNECTION` beserta variabel `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env`, lalu buat databasenya secara manual.

6. **Jalankan migrasi**
   ```bash
   php artisan migrate
   ```

7. **(Opsional) Jalankan seeder**
   ```bash
   php artisan db:seed
   ```
   Perintah ini akan membuat 1 user contoh (`test@example.com`). Lihat [Import Data Contoh](#import-data-contoh-csv) untuk mengisi data produk/transaksi dalam jumlah besar dari CSV, dan [Akun & Role](#akun--role) untuk cara membuat akun admin.

8. **Buat symbolic link storage** (dibutuhkan agar gambar produk, varian, dan bukti pembayaran bisa diakses publik)
   ```bash
   php artisan storage:link
   ```

9. **Install dependency frontend & build asset**
   ```bash
   npm install
   npm run build
   ```

> Alternatif: seluruh langkah 2–9 (kecuali storage:link) bisa dijalankan sekaligus lewat script bawaan:
> ```bash
> composer setup
> ```

## Menjalankan Aplikasi

### Mode pengembangan (server + queue + log + Vite sekaligus)
```bash
composer dev
```
Perintah ini menjalankan `php artisan serve`, `php artisan queue:listen`, `php artisan pail` (log viewer), dan `npm run dev` secara bersamaan.

### Menjalankan manual
```bash
php artisan serve
```
lalu, di terminal terpisah:
```bash
npm run dev
```

Aplikasi akan dapat diakses di `http://localhost:8000` (atau URL yang ditampilkan oleh `php artisan serve`).

## Akun & Role

Aplikasi ini punya dua role: `admin` dan `customer`. Registrasi melalui halaman `/register` akan membuat akun dengan role `customer` secara default. **Hanya boleh ada satu akun admin** di sistem ini.

### Akun Admin Default

| Email | Password |
|-------|----------|
| `admin@gmail.com` | `password123` |

> ⚠️ Aplikasi ini **belum punya fitur ganti password** dari dalam sistem (halaman profil hanya bisa mengubah jenis kelamin, kota, alamat, dan e-wallet favorit). Untuk mengganti password akun admin, lakukan lewat Tinker:
> ```bash
> php artisan tinker
> ```
> ```php
> App\Models\User::where('email', 'admin@gmail.com')->update([
>     'password' => bcrypt('password_baru_kamu'),
> ]);
> ```

Untuk membuat akun **admin** baru (mis. jika akun admin default belum ada di database), gunakan Tinker:
```bash
php artisan tinker
```
```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);
```
Atau ubah role user yang sudah terdaftar:
```php
App\Models\User::where('email', 'user@example.com')->update(['role' => 'admin']);
```

Setelah login, pengguna otomatis diarahkan sesuai role:
- `admin` → `/dashboard`
- `customer` → `/katalog`

## Konfigurasi Pembayaran

Informasi rekening bank dan e-wallet tujuan pembayaran diatur di `config/pembayaran.php`:

```php
return [
    'bank' => [
        'nama_bank'   => 'BCA',
        'no_rekening' => '1234567890',
        'atas_nama'   => 'Toko Fashion Sales',
    ],
    'qris_image' => null, // isi mis. 'images/qris.png' untuk menampilkan QRIS
    'ewallet' => [
        'nomor_tujuan' => '081234567890',
        'atas_nama'    => 'Toko Fashion Sales',
    ],
];
```

Sesuaikan nilai-nilai ini dengan rekening/QRIS toko sebelum digunakan di produksi. Data ini ditampilkan ke customer pada halaman detail pesanan selama status pembayaran masih *Menunggu Pembayaran*/*Ditolak*.

## Import Data Contoh (CSV)

Project ini menyertakan `public/data_fashion.csv` dan seeder khusus `FashionDataSeeder` untuk mengisi database dengan data pelanggan, produk, platform, dan transaksi dalam jumlah besar (format tanggal `d/m/Y H:i`, angka dengan pemisah titik ala Indonesia).

Jalankan:
```bash
php artisan db:seed --class=FashionDataSeeder
```

## Struktur Direktori

```
app/
  Http/
    Controllers/    # AuthController, ProdukController, TransaksiController,
                     # CustomerController, CartController, DashboardController,
                     # PelangganController, NotifikasiController
    Middleware/      # RoleMiddleware (pembatasan akses per role)
  Models/            # User, Pelanggan, Produk, ProdukVarian, Platform,
                     # Transaksi, DetailTransaksi, Keranjang, Notifikasi
database/
  migrations/        # Skema seluruh tabel
  seeders/           # DatabaseSeeder, FashionDataSeeder
resources/
  views/
    auth/            # Login & register
    customer/        # Katalog, keranjang, pesanan, promo, profil
    transaksi/       # Dashboard admin, CRUD produk, manajemen transaksi
    layouts/         # Layout utama & layout auth
routes/
  web.php            # Seluruh route aplikasi (auth, customer, admin)
public/
  data_fashion.csv   # Data contoh untuk FashionDataSeeder
```

## Routing Ringkas

| Area | Middleware | Contoh Route |
|------|-----------|---------------|
| Publik | – | `GET /login`, `GET /register` |
| Customer | `auth`, `role:customer` | `GET /katalog`, `GET /keranjang`, `POST /checkout`, `GET /pesanan-saya`, `GET /promo`, `GET /profil` |
| Bersama (login apa saja) | `auth` | `POST /notifikasi/baca-semua` |
| Admin | `auth`, `role:admin` | `GET /dashboard`, `GET /transaksi`, `GET /produk`, `GET /pelanggan` |

Route root (`/`) otomatis mengarahkan pengguna sesuai status login dan role.

## Testing

```bash
composer test
```
atau langsung:
```bash
php artisan test
```

## Lisensi

Project ini dibangun di atas starter kit [Laravel](https://laravel.com) yang berlisensi [MIT](https://opensource.org/licenses/MIT).
