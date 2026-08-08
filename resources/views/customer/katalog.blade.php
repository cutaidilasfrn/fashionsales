@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Katalog Produk Fashion</h4>
        <a href="{{ route('customer.pesanan.index') }}" class="btn btn-outline-secondary">Riwayat Pesanan Saya</a>
    </div>

    <div class="row">
        @foreach($produks as $produk)
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    @if($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" class="card-img-top" style="height: 220px; object-fit: cover;" alt="{{ $produk->nama_produk }}">
                    @else
                        <div class="bg-light text-center py-5 text-muted">No Image</div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        @if($produk->diskon_persen > 0)
                            <span class="badge bg-danger mb-1" style="width: fit-content;">Promo -{{ $produk->diskon_persen }}%</span>
                        @endif
                        <h6 class="card-title fw-bold">{{ $produk->nama_produk }}</h6>
                        <p class="card-text text-muted mb-1"><small>Bahan: {{ $produk->material ?? '-' }}</small></p>
                        @if($produk->deskripsi)
                            <p class="card-text text-muted mb-1"><small>{{ \Illuminate\Support\Str::limit($produk->deskripsi, 70) }}</small></p>
                        @endif

                        @if($produk->diskon_persen > 0)
                            <div class="mt-auto">
                                <small class="text-muted text-decoration-line-through">Rp {{ number_format($produk->harga_satuan, 0, ',', '.') }}</small>
                                <h6 class="text-danger mb-0">Rp {{ number_format($produk->harga_promo, 0, ',', '.') }}</h6>
                            </div>
                        @else
                            <h6 class="text-primary mt-auto">Rp {{ number_format($produk->harga_satuan, 0, ',', '.') }}</h6>
                        @endif

                        @if($produk->total_stok <= 0)
                            <small class="text-danger">Stok habis</small>
                        @else
                            <small class="text-muted">Sisa stok: {{ $produk->total_stok }}</small>
                        @endif

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#cartModal{{ $produk->id }}" {{ $produk->total_stok <= 0 ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus"></i> Keranjang
                            </button>
                            <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#orderModal{{ $produk->id }}" {{ $produk->total_stok <= 0 ? 'disabled' : '' }}>
                                Beli Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @include('customer.partials._produk_modal', [
                'produk' => $produk,
                'modalId' => 'orderModal' . $produk->id,
                'idPrefix' => 'katalog-beli-' . $produk->id,
                'formAction' => route('customer.checkout'),
                'submitLabel' => 'Konfirmasi Order',
                'submitClass' => 'btn-success',
                'showPembayaran' => true,
                'ewalletFavorit' => $ewalletFavorit,
            ])

            @include('customer.partials._produk_modal', [
                'produk' => $produk,
                'modalId' => 'cartModal' . $produk->id,
                'idPrefix' => 'katalog-cart-' . $produk->id,
                'formAction' => route('customer.keranjang.store'),
                'submitLabel' => 'Tambah ke Keranjang',
                'submitClass' => 'btn-primary',
                'showPembayaran' => false,
                'ewalletFavorit' => null,
            ])
        @endforeach
    </div>
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
