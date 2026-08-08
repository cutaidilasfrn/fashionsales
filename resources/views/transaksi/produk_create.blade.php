@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Tambah Produk Baru</h3>
        <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">
            <i class="bi me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Informasi Utama Produk --}}
                <h5 class="fw-bold mb-3 text-primary">Informasi Produk</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" value="{{ old('nama_produk') }}" required placeholder="Contoh: Kaos Polos Cotton Combed">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga_satuan" class="form-control" value="{{ old('harga_satuan') }}" min="0" required placeholder="100000">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Diskon Promo (%)</label>
                        <input type="number" name="diskon_persen" class="form-control" value="{{ old('diskon_persen', 0) }}" min="0" max="100" placeholder="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bahan / Material</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material') }}" placeholder="Contoh: Cotton Combed 30s">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Gambar Utama (Cover/Thumbnail)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <small class="text-muted">Opsional — kalau kosong, otomatis pakai gambar warna pertama yang kamu unggah di bawah.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan deskripsi lengkap produk di sini...">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Pilih Warna --}}
                <h5 class="fw-bold mb-2 text-primary">Warna yang Tersedia (maks. {{ \App\Models\Produk::MAX_WARNA_PER_PRODUK }} warna)</h5>
                <p class="text-muted small mb-3">Centang warna yang benar-benar tersedia untuk produk ini. Tiap warna dicentang bisa diberi gambar sendiri + stok per ukuran di bawah.</p>

                <div class="d-flex flex-wrap gap-3 mb-3">
                    @foreach(\App\Models\Produk::WARNA_OPTIONS as $i => $warna)
                        <div class="form-check">
                            <input class="form-check-input warna-checkbox" type="checkbox" name="warna[]"
                                   value="{{ $warna }}" id="warna-{{ $i }}"
                                   {{ in_array($warna, old('warna', [])) ? 'checked' : '' }}
                                   onchange="toggleWarnaPanel(this, {{ $i }})">
                            <label class="form-check-label" for="warna-{{ $i }}">{{ $warna }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Panel per warna: gambar + stok per ukuran --}}
                <div id="warna-panels" class="mb-4">
                    @foreach(\App\Models\Produk::WARNA_OPTIONS as $i => $warna)
                        @php $tampil = in_array($warna, old('warna', [])); @endphp
                        <div class="warna-panel border rounded-3 p-3 mb-3 bg-light {{ $tampil ? '' : 'd-none' }}" id="panel-{{ $i }}" data-warna="{{ $warna }}">
                            <h6 class="fw-bold mb-3">Warna: {{ $warna }}</h6>

                            <div class="mb-3">
                                <label class="form-label">Gambar untuk warna {{ $warna }} (opsional)</label>
                                <input type="file" name="gambar_warna[{{ $warna }}]" class="form-control" accept="image/*">
                            </div>

                            <label class="form-label">Stok per Ukuran</label>
                            <div class="row g-2">
                                @foreach(\App\Models\Produk::UKURAN_OPTIONS as $ukuran)
                                    <div class="col-6 col-md-3">
                                        <label class="form-label small text-muted mb-1">{{ $ukuran }}</label>
                                        <input type="number" name="stok_varian[{{ $warna }}][{{ $ukuran }}]"
                                               class="form-control" min="0"
                                               value="{{ old('stok_varian.'.$warna.'.'.$ukuran, 0) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('produk.index') }}" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const MAX_WARNA = {{ \App\Models\Produk::MAX_WARNA_PER_PRODUK }};

    function toggleWarnaPanel(checkbox, index) {
        const checked = document.querySelectorAll('.warna-checkbox:checked');

        if (checked.length > MAX_WARNA) {
            checkbox.checked = false;
            alert('Maksimal ' + MAX_WARNA + ' warna per produk ya, biar nggak kewalahan urus gambar & stoknya.');
            return;
        }

        const panel = document.getElementById('panel-' + index);
        panel.classList.toggle('d-none', !checkbox.checked);
    }
</script>
@endsection
