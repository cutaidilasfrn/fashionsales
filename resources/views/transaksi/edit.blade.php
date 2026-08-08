@extends('layouts.app')

@section('content')

<style>
    .edit-hero {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
        border-radius: 18px;
        padding: 28px 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, .25);
    }
    .edit-hero::before {
        content: "";
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: rgba(255,255,255,.08);
        border-radius: 50%;
    }
    .edit-hero::after {
        content: "";
        position: absolute;
        bottom: -80px;
        right: 120px;
        width: 160px;
        height: 160px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .pulse-dot {
        width: 9px;
        height: 9px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        box-shadow: 0 0 0 rgba(34,197,94,.6);
        animation: pulse 1.6s infinite;
    }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.6); }
        70%  { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
    .form-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,.06);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .form-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f1f4;
        font-weight: 700;
        padding: 16px 22px;
    }
    .form-card .card-body { padding: 22px; }

    .item-row {
        background: #f8f9ff;
        border: 1px solid #ececfb;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 14px;
        transition: all .2s;
    }
    .item-row:hover { border-color: #c7c7f5; }

    .summary-card {
        border-radius: 16px;
        border: none;
        background: linear-gradient(160deg, #1e1b4b 0%, #4338ca 100%);
        color: #fff;
        position: sticky;
        top: 20px;
        overflow: hidden;
    }
    .summary-card .card-body { padding: 24px; }
    .summary-line {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: .92rem;
        color: rgba(255,255,255,.8);
        border-bottom: 1px dashed rgba(255,255,255,.15);
    }
    .summary-total {
        font-size: 1.7rem;
        font-weight: 800;
        margin-top: 10px;
    }
    .btn-save-gradient {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border: none;
        font-weight: 700;
        padding: 12px;
        border-radius: 12px;
        transition: transform .15s;
    }
    .btn-save-gradient:hover { transform: translateY(-2px); color: #fff; }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.18);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: .85rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }
</style>

<div class="edit-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="status-chip mb-2"><span class="pulse-dot"></span> Mode Edit Transaksi</div>
        <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i>Ubah Transaksi</h2>
        <p class="mb-0 opacity-75">Kode: <strong>{{ $transaksi->kode_transaksi }}</strong> &middot; Dibuat {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d M Y') }}</p>
    </div>
    <a href="{{ route('transaksi.index') }}" class="btn btn-light fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('transaksi.update', $transaksi->id) }}" method="POST" id="edit-form">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- KIRI -->
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-header"><i class="bi bi-person-fill text-primary me-2"></i>Informasi Pelanggan & Platform</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pelanggan <span class="text-danger">*</span></label>
                            <select name="pelanggan_id" class="form-select" required>
                                @foreach($pelanggans as $p)
                                    <option value="{{ $p->id }}" @selected($transaksi->pelanggan_id == $p->id)>
                                        {{ $p->nama_pelanggan }} ({{ $p->jenis_kelamin }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Platform <span class="text-danger">*</span></label>
                            <select name="platform_id" class="form-select" required>
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform->id }}" @selected($transaksi->platform_id == $platform->id)>
                                        {{ $platform->nama_platform }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-fill text-primary me-2"></i>Produk Transaksi</span>
                    <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                    </button>
                </div>
                <div class="card-body">
                    <div id="item-wrapper">
                        @foreach($transaksi->detailTransaksis as $i => $detail)
                        <div class="row g-2 item-row align-items-end">
                            <div class="col-md-5">
                                <label class="form-label text-muted small fw-semibold">Produk</label>
                                <select name="produk_id[]" class="form-select produk-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}" data-harga="{{ $p->harga_satuan }}" @selected($detail->produk_id == $p->id)>
                                            {{ $p->nama_produk }} - Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Ukuran</label>
                                <select name="ukuran[]" class="form-select">
                                    @foreach(['S','M','L','XL'] as $ukuran)
                                        <option value="{{ $ukuran }}" @selected($detail->ukuran == $ukuran)>{{ $ukuran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Qty</label>
                                <input type="number" name="kuantitas[]" class="form-control qty-input" value="{{ $detail->kuantitas }}" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Subtotal</label>
                                <input type="text" class="form-control bg-white subtotal-display" value="Rp {{ number_format($detail->subtotal,0,',','.') }}" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row" title="Hapus Item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN -->
        <div class="col-lg-4">
            <div class="card form-card">
                <div class="card-header"><i class="bi bi-info-circle-fill text-primary me-2"></i>Status & Pembayaran</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" class="form-select" required>
                            @foreach(['Transfer Bank','E-Wallet','Kartu Kredit'] as $metode)
                                <option value="{{ $metode }}" @selected($transaksi->metode_pembayaran == $metode)>{{ $metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_transaksi" class="form-control"
                               value="{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label text-muted small fw-semibold">Status Pesanan <span class="text-danger">*</span></label>
                        <select name="status_pesanan" class="form-select" required>
                            @foreach(['Selesai','Pending','Batal'] as $status)
                                <option value="{{ $status }}" @selected($transaksi->status_pesanan == $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card summary-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-receipt-cutoff me-2"></i>Ringkasan Belanja</h6>
                    <div class="summary-line">
                        <span>Jumlah Item</span>
                        <span id="summary-item-count">0</span>
                    </div>
                    <div class="summary-line">
                        <span>Total Qty</span>
                        <span id="summary-qty-count">0</span>
                    </div>
                    <div class="summary-total">
                        Rp <span id="summary-grand-total">0</span>
                    </div>
                    <button type="submit" class="btn btn-save-gradient w-100 text-white mt-4">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const wrapper = document.getElementById('item-wrapper');

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(Math.round(num));
    }

    function recalcRow(row) {
        const select = row.querySelector('.produk-select');
        const qtyInput = row.querySelector('.qty-input');
        const subtotalDisplay = row.querySelector('.subtotal-display');

        const harga = select.selectedOptions[0] ? parseFloat(select.selectedOptions[0].dataset.harga || 0) : 0;
        const qty = parseFloat(qtyInput.value || 0);
        const subtotal = harga * qty;

        subtotalDisplay.value = 'Rp ' + formatRupiah(subtotal);
        return subtotal;
    }

    function recalcSummary() {
        let grandTotal = 0;
        let totalQty = 0;
        const rows = document.querySelectorAll('.item-row');

        rows.forEach(row => {
            grandTotal += recalcRow(row);
            totalQty += parseFloat(row.querySelector('.qty-input').value || 0);
        });

        document.getElementById('summary-item-count').textContent = rows.length;
        document.getElementById('summary-qty-count').textContent = totalQty;
        document.getElementById('summary-grand-total').textContent = formatRupiah(grandTotal);
    }

    document.getElementById('add-item').addEventListener('click', function () {
        const firstRow = wrapper.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.qty-input').value = 1;
        newRow.querySelector('.produk-select').selectedIndex = 0;
        newRow.querySelector('.subtotal-display').value = 'Rp 0';
        wrapper.appendChild(newRow);
        recalcSummary();
    });

    wrapper.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                recalcSummary();
            }
        }
    });

    wrapper.addEventListener('change', function (e) {
        if (e.target.classList.contains('produk-select') || e.target.classList.contains('qty-input')) {
            recalcSummary();
        }
    });
    wrapper.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input')) {
            recalcSummary();
        }
    });

    // Hitung ringkasan awal saat halaman dibuka
    recalcSummary();
</script>
@endsection
