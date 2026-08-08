@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Data Pelanggan</h2>
            <p class="text-muted mb-0">Daftar seluruh pelanggan beserta jumlah transaksi</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggan.index') }}" class="row mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama pelanggan, kota, atau gender..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
                <div class="col-md-7"></div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Jenis Kelamin</th>
                            <th>Kota</th>
                            <th class="text-center">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggans as $pelanggan)
                        <tr>
                            <td>{{ $loop->iteration + ($pelanggans->firstItem() - 1) }}</td>
                            <td>{{ $pelanggan->nama_pelanggan }}</td>
                            <td>{{ $pelanggan->jenis_kelamin }}</td>
                            <td>{{ $pelanggan->kota }}</td>
                            <td class="text-center"><span class="badge bg-success">{{ $pelanggan->total_transaksi }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data pelanggan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $pelanggans->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection