@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Fashion Sales</h3>
                        <p class="text-muted small">Buat akun customer baru</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('register.process') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">NAMA LENGKAP</label>
                            <input type="text" name="name" class="form-control bg-light border-0 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autofocus>
                            @error('name')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">EMAIL</label>
                            <input type="email" name="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@email.com" required>
                            @error('email')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">PASSWORD</label>
                            <input type="password" name="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                            @error('password')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">KONFIRMASI PASSWORD</label>
                            <input type="password" name="password_confirmation" class="form-control bg-light border-0" placeholder="Ulangi password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-3">Daftar Sekarang</button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted small">Sudah punya akun? </span>
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold small">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection