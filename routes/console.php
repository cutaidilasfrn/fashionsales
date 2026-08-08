<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Transaksi;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Versi "resmi" dari auto-batal 1x24 jam, buat dipakai kalau aplikasi ini
// di-deploy ke server sungguhan dengan cron aktif (crontab jalanin
// `php artisan schedule:run` tiap menit). Di lingkungan lokal tanpa cron,
// pengecekan yang sama juga jalan tiap request lewat AppServiceProvider,
// jadi fitur ini tetap berfungsi walau perintah ini tidak pernah dieksekusi.
Artisan::command('transaksi:batalkan-kadaluarsa', function () {
    Transaksi::batalkanYangKadaluarsa();
    $this->info('Pesanan yang lewat batas waktu pembayaran sudah dibatalkan.');
})->purpose('Batalkan pesanan yang lewat 1x24 jam tanpa pembayaran terverifikasi');

Artisan::command('transaksi:selesaikan-kadaluarsa', function () {
    Transaksi::selesaikanYangKadaluarsa();
    $this->info('Pesanan Dikirim yang lewat 7 hari tanpa konfirmasi sudah ditandai Selesai.');
})->purpose('Auto-selesaikan pesanan Dikirim yang tidak dikonfirmasi customer dalam 7 hari');

Schedule::command('transaksi:batalkan-kadaluarsa')->hourly();
Schedule::command('transaksi:selesaikan-kadaluarsa')->daily();
