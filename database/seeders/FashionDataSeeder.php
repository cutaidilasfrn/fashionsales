<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pelanggan;
use App\Models\Platform;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;

class FashionDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Cari file CSV di folder public
        $file = public_path('data_fashion.csv');
        
        if (!file_exists($file)) {
            $this->command->error('File data_fashion.csv tidak ditemukan di folder public!');
            return;
        }

        // 2. Buka file
        $handle = fopen($file, 'r');
        
        // Lewati baris pertama (Judul kolom)
        fgetcsv($handle, 0, ';');

        $this->command->info('Mulai membaca dan memasukkan 15.000 data... Ini mungkin butuh waktu beberapa detik.');

        // Gunakan transaksi database agar aman & cepat
        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                // Lewati jika baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                // --- PROSES PARSING DATA ---
                // Ubah format tanggal dari "13/12/2025 16:02" ke "Y-m-d H:i:s"
                $tanggalOrder = Carbon::createFromFormat('d/m/Y H:i', $row[0])->format('Y-m-d H:i:s');
                
                // Hilangkan titik pada nominal uang agar jadi integer yang valid
                $hargaSatuan   = (int) str_replace('.', '', $row[4]);
                $subtotal      = (int) str_replace('.', '', $row[7]);
                $totalDiskon   = (int) str_replace('.', '', $row[8]);
                $ongkir        = (int) str_replace('.', '', $row[10]);
                $grandTotal    = (int) str_replace('.', '', $row[11]);

                $pelanggan = Pelanggan::firstOrCreate(
                    ['nama_pelanggan' => trim($row[1]), 'kota' => trim($row[2])], 
                    ['jenis_kelamin' => trim($row[18])]
                );

                // --- 2. TABEL PLATFORM ---
                $platform = Platform::firstOrCreate(
                    ['nama_platform' => trim($row[14])]
                );

                // --- 3. TABEL PRODUK ---
                // Dicari berdasarkan nama_produk SAJA, karena kolom itu sekarang unique.
                // Kalau nama sama tapi material di CSV beda-beda, data yang dipakai
                // adalah dari kemunculan pertama.
                $produk = Produk::firstOrCreate(
    [
        'nama_produk' => trim($row[3]),
    ],
    [
        'material' => trim($row[17]),
        'harga_satuan' => $hargaSatuan
    ]
);

               $transaksi = Transaksi::firstOrCreate(
[
    'pelanggan_id'       => $pelanggan->id,
    'tanggal_transaksi'  => $tanggalOrder,
    'platform_id'        => $platform->id,
    'metode_pembayaran'  => trim($row[12]),
    'grand_total'        => $grandTotal
],
[
    'kode_transaksi'     => 'TRX' . str_pad($count + 1, 6, '0', STR_PAD_LEFT),
    'status_pesanan'     => trim($row[13]),
    'biaya_pengiriman'   => $ongkir,
    'total_diskon'       => $totalDiskon
]
);

                // --- 5. TABEL DETAIL TRANSAKSI ---
                DetailTransaksi::firstOrCreate(
[
    'transaksi_id' => $transaksi->id,
    'produk_id'    => $produk->id,
    'ukuran'       => trim($row[16])
],
[
    'kuantitas'    => (int) $row[5],
    'harga_satuan' => $hargaSatuan,
    'diskon'       => (int) $row[6],
    'subtotal'     => $subtotal
]
);

                $count++;
                
                // Simpan per 1000 baris agar RAM laptop tidak kewalahan
                if ($count % 1000 == 0) {
                    DB::commit();
                    $this->command->info("Berhasil insert $count baris...");
                    DB::beginTransaction();
                }
            }
            
            DB::commit(); // Simpan sisa datanya
            $this->command->info("Selesai! Total $count baris data sukses dimasukkan ke 5 tabel.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Terjadi error di baris ke-" . ($count + 1) . ": " . $e->getMessage());
        }

        fclose($handle);
    }
}