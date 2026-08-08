<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with('varians')->orderBy('nama_produk')->get();
        return view('transaksi.produk', compact('produks'));
    }

    public function create()
    {
        return view('transaksi.produk_create');
    }

    private function validasiWarnaStok(Request $request, array $rules): array
    {
        return $request->validate(array_merge($rules, [
            'warna'         => 'required|array|min:1|max:' . Produk::MAX_WARNA_PER_PRODUK,
            'warna.*'       => 'in:' . implode(',', Produk::WARNA_OPTIONS),
            'gambar_warna.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stok_varian'   => 'required|array',
            'stok_varian.*.*' => 'required|integer|min:0',
        ]), [
            'warna.required' => 'Pilih minimal 1 warna untuk produk ini.',
            'warna.max'      => 'Maksimal ' . Produk::MAX_WARNA_PER_PRODUK . ' warna per produk, biar tidak kewalahan mengurus gambar & stoknya.',
        ]);
    }

    // Simpan/perbarui baris produk_varians untuk warna-warna yang dicentang saja,
    // lalu bersihkan warna & gambar yang tadinya ada tapi sekarang tidak dicentang lagi.
    private function simpanVarian(Request $request, Produk $produk, ?array $warnaLama = null): void
    {
        foreach ($request->warna as $warna) {
            $pathGambarWarna = null;

            if ($request->hasFile("gambar_warna.$warna")) {
                // Hapus gambar lama warna ini (kalau ada) sebelum ganti yang baru
                $lama = $produk->varians()->where('warna', $warna)->whereNotNull('gambar')->first();
                if ($lama && $lama->gambar && Storage::disk('public')->exists($lama->gambar)) {
                    Storage::disk('public')->delete($lama->gambar);
                }
                $pathGambarWarna = $request->file("gambar_warna.$warna")->store('produk/varian', 'public');
            }

            foreach (Produk::UKURAN_OPTIONS as $ukuran) {
                $data = ['stok' => (int) ($request->input("stok_varian.$warna.$ukuran") ?? 0)];
                if ($pathGambarWarna) {
                    $data['gambar'] = $pathGambarWarna;
                }

                ProdukVarian::updateOrCreate(
                    ['produk_id' => $produk->id, 'ukuran' => $ukuran, 'warna' => $warna],
                    $data
                );
            }
        }

        // Bersihkan warna yang tadinya ada tapi sekarang dihapus centangnya (khusus edit)
        if ($warnaLama !== null) {
            $warnaDihapus = array_diff($warnaLama, $request->warna);

            if (! empty($warnaDihapus)) {
                $variasiDihapus = $produk->varians()->whereIn('warna', $warnaDihapus)->get();
                foreach ($variasiDihapus as $v) {
                    if ($v->gambar && Storage::disk('public')->exists($v->gambar)) {
                        Storage::disk('public')->delete($v->gambar);
                    }
                }
                $produk->varians()->whereIn('warna', $warnaDihapus)->delete();
            }
        }
    }

    public function store(Request $request)
    {
        $this->validasiWarnaStok($request, [
            'nama_produk'   => 'required|string|max:255|unique:produks,nama_produk',
            'harga_satuan'  => 'required|numeric|min:0',
            'material'      => 'nullable|string|max:255',
            'deskripsi'     => 'nullable|string|max:2000',
            'diskon_persen' => 'nullable|integer|min:0|max:100',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only(['nama_produk', 'harga_satuan', 'material', 'deskripsi']);
            $data['diskon_persen'] = $request->diskon_persen ?? 0;

            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }

            $produk = Produk::create($data);

            $this->simpanVarian($request, $produk);

            // Kalau tidak ada gambar cover diupload, pakai gambar warna pertama sebagai cover.
            if (! $produk->gambar) {
                $gambarPertama = $produk->varians()->whereNotNull('gambar')->value('gambar');
                if ($gambarPertama) {
                    $produk->update(['gambar' => $gambarPertama]);
                }
            }
        });

        return redirect()->route('produk.index')->with('success', 'Produk & varian stok berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $produk->load('varians');
        return view('transaksi.produk_edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $this->validasiWarnaStok($request, [
            'nama_produk'   => ['required', 'string', 'max:255', Rule::unique('produks', 'nama_produk')->ignore($produk->id)],
            'harga_satuan'  => 'required|numeric|min:0',
            'material'      => 'nullable|string|max:255',
            'deskripsi'     => 'nullable|string|max:2000',
            'diskon_persen' => 'nullable|integer|min:0|max:100',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $warnaLama = $produk->varians()->pluck('warna')->unique()->values()->all();

        DB::transaction(function () use ($request, $produk, $warnaLama) {
            $data = $request->only(['nama_produk', 'harga_satuan', 'material', 'deskripsi']);
            $data['diskon_persen'] = $request->diskon_persen ?? 0;

            if ($request->hasFile('gambar')) {
                if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                    Storage::disk('public')->delete($produk->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }

            $produk->update($data);

            $this->simpanVarian($request, $produk, $warnaLama);

            // Kalau gambar cover sekarang kosong (belum pernah upload & tidak diganti),
            // pakai gambar warna pertama yang tersedia.
            $produk->refresh();
            if (! $produk->gambar) {
                $gambarPertama = $produk->varians()->whereNotNull('gambar')->value('gambar');
                if ($gambarPertama) {
                    $produk->update(['gambar' => $gambarPertama]);
                }
            }
        });

        return redirect()->route('produk.index')->with('success', 'Data produk & stok varian berhasil diperbarui.');
    }

    // Nambah stok untuk 1 kombinasi warna + ukuran tertentu dari sebuah produk.
    public function tambahStok(Request $request, Produk $produk)
    {
        $warnaTersedia = $produk->varians()->pluck('warna')->unique()->values()->all();

        $request->validate([
            'warna'  => 'required|in:' . implode(',', $warnaTersedia ?: ['-']),
            'ukuran' => 'required|in:' . implode(',', Produk::UKURAN_OPTIONS),
            'jumlah' => 'required|integer|min:1',
        ]);

        $varian = ProdukVarian::firstOrCreate(
            ['produk_id' => $produk->id, 'warna' => $request->warna, 'ukuran' => $request->ukuran],
            ['stok' => 0]
        );
        $varian->increment('stok', $request->jumlah);

        return back()->with(
            'success',
            "Stok \"{$produk->nama_produk}\" ({$request->warna}, {$request->ukuran}) berhasil ditambah {$request->jumlah}. Stok kombinasi ini sekarang: {$varian->fresh()->stok}."
        );
    }

    public function destroy(Produk $produk)
    {
        foreach ($produk->varians as $v) {
            if ($v->gambar && Storage::disk('public')->exists($v->gambar)) {
                Storage::disk('public')->delete($v->gambar);
            }
        }

        if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
            Storage::disk('public')->delete($produk->gambar);
        }

        // Baris produk_varians ikut terhapus otomatis lewat cascadeOnDelete() di migration.
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
