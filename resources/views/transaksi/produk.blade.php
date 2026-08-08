@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Daftar Produk</h4>
        <a href="{{ route('produk.create') }}" class="btn btn-primary">+ Tambah Produk</a>
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 90px;">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Material</th>
                            <th>Harga Satuan</th>
                            <th>Diskon</th>
                            <th>Stok</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($p->gambar)
                                        <img src="{{ asset('storage/' . $p->gambar) }}" alt="{{ $p->nama_produk }}" style="width: 60px; height: 60px; object-fit: cover;" class="rounded border">
                                    @else
                                        <span class="badge bg-secondary">No Image</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $p->nama_produk }}</td>
                                <td>{{ $p->material ?? '-' }}</td>
                                <td>Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}</td>
                                <td>
                                    @if($p->diskon_persen > 0)
                                        <span class="badge bg-danger">{{ $p->diskon_persen }}%</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->total_stok <= \App\Models\Produk::BATAS_STOK_MENIPIS)
                                        <span class="badge bg-warning text-dark mb-1" title="Stok menipis">Total: {{ $p->total_stok }}</span>
                                    @else
                                        <span class="badge bg-light text-dark border mb-1">Total: {{ $p->total_stok }}</span>
                                    @endif
                                    <div class="small text-muted mt-1">
                                        @forelse($p->varians->groupBy('warna') as $warna => $baris)
                                            <div>{{ $warna }}:
                                                @foreach($baris as $v)
                                                    {{ $v->ukuran }}={{ $v->stok }}@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                        @empty
                                            <span class="fst-italic">Belum ada warna/stok diatur</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success me-1" data-bs-toggle="modal" data-bs-target="#stokModal{{ $p->id }}">
                                        + Stok
                                    </button>
                                    <a href="{{ route('produk.edit', $p->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                                    <form action="{{ route('produk.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Tambah Stok -->
                            <div class="modal fade" id="stokModal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('produk.tambah-stok', $p->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tambah Stok: {{ $p->nama_produk }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted mb-2">Total stok saat ini: <strong>{{ $p->total_stok }}</strong></p>

                                                <label class="form-label">Warna</label>
                                                <select name="warna" class="form-select mb-2" required>
                                                    @foreach($p->varians->pluck('warna')->unique() as $warna)
                                                        <option value="{{ $warna }}">{{ $warna }}</option>
                                                    @endforeach
                                                </select>

                                                <label class="form-label">Ukuran</label>
                                                <select name="ukuran" class="form-select mb-2" required>
                                                    @foreach(\App\Models\Produk::UKURAN_OPTIONS as $ukuran)
                                                        <option value="{{ $ukuran }}">{{ $ukuran }}</option>
                                                    @endforeach
                                                </select>

                                                <label class="form-label">Jumlah yang ditambahkan</label>
                                                <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Tambah Stok</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
