@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Tambah Transaksi Baru</h2>
        <p class="text-muted mb-0">Input data transaksi penjualan baru</p>
    </div>
    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<form action="{{ route('transaksi.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- SEKSI UTAMA (KIRI) -->
        <div class="col-lg-8">
            <!-- Card 1: Informasi Utama Pelanggan -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-person-fill text-primary me-2"></i> Informasi Pelanggan Utama
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pelanggan (contoh: Cut Aidila)" value="{{ old('nama_pelanggan') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="Perempuan">Perempuan</option>
                                <option value="Laki-laki">Laki-laki</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat pengiriman / domisili pelanggan">{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Card 2: Produk yang Dibeli -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-fill text-primary me-2"></i> Pilih Produk Transaksi</span>
                    <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Produk Lain
                    </button>
                </div>
                <div class="card-body">
                    <div id="item-wrapper">
                        <div class="row g-2 mb-3 item-row align-items-end">
                            <div class="col-md-5">
                                <label class="form-label text-muted small fw-semibold">Produk</label>
                                <select name="produk_id[]" class="form-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_produk }} - Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Ukuran</label>
                                <select name="ukuran[]" class="form-select">
                                    <option value="S">S</option>
                                    <option value="M" selected>M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Jumlah (Qty)</label>
                                <input type="number" name="kuantitas[]" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row" title="Hapus Item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEKSI INFO TRANSAKSI (KANAN) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> Detail Transaksi
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Kode Transaksi</label>
                        <input type="text" name="kode_transaksi" class="form-control bg-light fw-bold text-primary" value="{{ $kode_transaksi }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Platform Sales <span class="text-danger">*</span></label>
                        <select name="platform_id" class="form-select" required>
                            <option value="">-- Pilih Platform --</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform->id }}">{{ $platform->nama_platform }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet</option>
                            <option value="Kartu Kredit">Kartu Kredit</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_transaksi" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Status Pesanan <span class="text-danger">*</span></label>
                        <select name="status_pesanan" class="form-select" required>
                            <option value="Selesai">Selesai</option>
                            <option value="Pending">Pending</option>
                            <option value="Batal">Batal</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold fs-6">
                        <i class="bi bi-check-circle me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById('add-item').addEventListener('click', function () {
        let wrapper = document.getElementById('item-wrapper');
        let firstRow = wrapper.querySelector('.item-row');
        let newRow = firstRow.cloneNode(true);
        newRow.querySelector('input').value = 1;
        newRow.querySelector('select').selectedIndex = 0;
        wrapper.appendChild(newRow);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            let rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
            }
        }
    });
</script>
@endsection