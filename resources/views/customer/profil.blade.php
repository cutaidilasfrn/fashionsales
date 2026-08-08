@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 700px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-gear"></i> Profil Saya</h4>
        <p class="text-muted mb-0">Kelola data diri dan e-wallet favoritmu untuk mempercepat checkout.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <label class="form-label text-muted small">Nama</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                <small class="text-muted">Nama akun tidak bisa diubah dari sini.</small>
            </div>

            <form action="{{ route('customer.profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="Pria" {{ $pelanggan->jenis_kelamin == 'Pria' ? 'selected' : '' }}>Pria</option>
                        <option value="Wanita" {{ $pelanggan->jenis_kelamin == 'Wanita' ? 'selected' : '' }}>Wanita</option>
                        <option value="Lainnya" {{ $pelanggan->jenis_kelamin == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kota <span class="text-danger">*</span></label>
                    <input type="text" name="kota" class="form-control" value="{{ old('kota', $pelanggan->kota) }}" placeholder="Kota domisili" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat pengiriman" required>{{ old('alamat', $pelanggan->alamat) }}</textarea>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-wallet2 me-1"></i> E-Wallet Favorit</label>
                    <select name="ewallet_favorit" class="form-select">
                        <option value="">- Belum ada -</option>
                        @foreach(\App\Models\Pelanggan::EWALLET_OPTIONS as $ewallet)
                            <option value="{{ $ewallet }}" {{ $pelanggan->ewallet_favorit == $ewallet ? 'selected' : '' }}>{{ $ewallet }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        E-wallet ini akan otomatis terpilih setiap kamu checkout pakai metode E-Wallet.
                        Kamu tetap bisa ganti providernya per-pesanan.
                    </small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
