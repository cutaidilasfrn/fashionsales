<?php

// Info tujuan pembayaran toko. Ganti nilainya sesuai rekening/QRIS toko asli.
// Ditampilkan ke pembeli di halaman Detail Pesanan saat status masih
// "Menunggu Pembayaran" / "Ditolak", supaya pembeli tahu harus transfer ke mana.

return [

    'bank' => [
        'nama_bank'    => 'BCA',
        'no_rekening'  => '1234567890',
        'atas_nama'    => 'Toko Fashion Sales',
    ],

    // Opsional. Kalau mau tampilkan QR code QRIS, taruh gambarnya di
    // public/images/qris.png lalu isi baris di bawah ini dengan 'images/qris.png'.
    // Kalau dikosongkan (null) atau file-nya belum ada, bagian QRIS otomatis disembunyikan.
    'qris_image' => null,

    // Nomor tujuan e-wallet toko. Satu nomor ini dipakai untuk semua provider
    // (OVO/GoPay/DANA/dll) karena kebanyakan toko kecil cuma punya satu nomor HP
    // yang terdaftar di beberapa e-wallet sekaligus. Ganti sesuai nomor toko asli.
    'ewallet' => [
        'nomor_tujuan' => '081234567890',
        'atas_nama'    => 'Toko Fashion Sales',
    ],

];
