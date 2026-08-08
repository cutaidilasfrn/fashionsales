@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-tags text-danger"></i> Promo Spesial</h4>
            <p class="text-muted mb-0">Produk pilihan dengan harga lagi didiskon, jangan sampai kehabisan!</p>
        </div>
        <a href="{{ route('customer.katalog') }}" class="btn btn-outline-secondary">Lihat Semua Produk</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($produks->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-tag fs-1 d-block mb-2"></i>
                Belum ada promo yang aktif saat ini. Cek lagi lain waktu ya!
            </div>
        </div>
    @else
        <div class="row">
            @foreach($produks as $produk)
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0 position-relative">
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="z-index: 2;">-{{ $produk->diskon_persen }}%</span>

                        @if($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="card-img-top" style="height: 220px; object-fit: cover;" alt="{{ $produk->nama_produk }}">
                        @else
                            <div class="bg-light text-center py-5 text-muted">No Image</div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold">{{ $produk->nama_produk }}</h6>
                            <p class="card-text text-muted mb-1"><small>Bahan: {{ $produk->material ?? '-' }}</small></p>
                            @if($produk->deskripsi)
                                <p class="card-text text-muted mb-1"><small>{{ \Illuminate\Support\Str::limit($produk->deskripsi, 70) }}</small></p>
                            @endif

                            <div class="mt-auto">
                                <small class="text-muted text-decoration-line-through">Rp {{ number_format($produk->harga_satuan, 0, ',', '.') }}</small>
                                <h6 class="text-danger mb-0">Rp {{ number_format($produk->harga_promo, 0, ',', '.') }}</h6>
                            </div>

                            @if($produk->total_stok <= 0)
                                <small class="text-danger">Stok habis</small>
                            @else
                                <small class="text-muted">Sisa stok: {{ $produk->total_stok }}</small>
                            @endif

                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-outline-danger flex-fill" data-bs-toggle="modal" data-bs-target="#promoCartModal{{ $produk->id }}" {{ $produk->total_stok <= 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-cart-plus"></i> Keranjang
                                </button>
                                <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#promoOrderModal{{ $produk->id }}" {{ $produk->total_stok <= 0 ? 'disabled' : '' }}>
                                    Beli Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('customer.partials._produk_modal', [
                    'produk' => $produk,
                    'modalId' => 'promoOrderModal' . $produk->id,
                    'idPrefix' => 'promo-beli-' . $produk->id,
                    'formAction' => route('customer.checkout'),
                    'submitLabel' => 'Konfirmasi Order',
                    'submitClass' => 'btn-success',
                    'showPembayaran' => true,
                    'ewalletFavorit' => $ewalletFavorit,
                ])

                @include('customer.partials._produk_modal', [
                    'produk' => $produk,
                    'modalId' => 'promoCartModal' . $produk->id,
                    'idPrefix' => 'promo-cart-' . $produk->id,
                    'formAction' => route('customer.keranjang.store'),
                    'submitLabel' => 'Tambah ke Keranjang',
                    'submitClass' => 'btn-danger',
                    'showPembayaran' => false,
                    'ewalletFavorit' => null,
                ])
            @endforeach
        </div>
    @endif
</div>

<script>
    // Tombol +/- kuantitas di modal produk (dipakai semua modal beli & keranjang)
    function ubahJumlahModal(inputId, delta, maxStok) {
        const input = document.getElementById(inputId);
        let nilai = (parseInt(input.value, 10) || 1) + delta;
        if (nilai < 1) nilai = 1;
        if (nilai > maxStok) nilai = maxStok;
        input.value = nilai;
    }

    // Tampilkan/sembunyikan pilihan provider e-wallet di modal Beli Sekarang
    function toggleEwalletModal(select, idPrefix) {
        const field = document.getElementById(idPrefix + '-ewallet-field');
        const ewalletSelect = field.querySelector('select[name="ewallet_provider"]');
        if (select.value === 'E-Wallet') {
            field.style.display = 'block';
            ewalletSelect.required = true;
        } else {
            field.style.display = 'none';
            ewalletSelect.required = false;
        }
    }
</script>
@endsection
