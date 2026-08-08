@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Fashion Sales</h3>
                        <p class="text-muted small">Silakan login untuk mengakses sistem</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('login.process') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">EMAIL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">📧</span>
                                <input type="email" name="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="admin@gmail.com" required autofocus>
                            </div>
                            @error('email')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">🔒</span>
                                <input type="password" name="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                            </div>
                            @error('password')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-3">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted small">Belum punya akun? </span>
                        <a href="{{ route('register') }}" class="text-decoration-none fw-bold small">Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection