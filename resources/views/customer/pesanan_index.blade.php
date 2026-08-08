@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Riwayat Pesanan Saya</h4>
        <a href="{{ route('customer.katalog') }}" class="btn btn-primary">
            <i class="bi bi-grid me-1"></i> Belanja Lagi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($pesanans->isEmpty())
                <p class="text-muted text-center py-4 mb-0">Kamu belum punya pesanan. Yuk mulai belanja!</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Platform</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanans as $pesanan)
                                @php $status = strtolower($pesanan->status_pesanan); @endphp
                                <tr>
                                    <td>{{ $pesanan->kode_transaksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pesanan->tanggal_transaksi)->format('d M Y H:i') }}</td>
                                    <td>{{ $pesanan->platform->nama_platform ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($status == 'selesai')
                                            <span class="badge bg-success">{{ $pesanan->status_pesanan }}</span>
                                        @elseif($status == 'pending')
                                            <span class="badge bg-warning text-dark">{{ $pesanan->status_pesanan }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $pesanan->status_pesanan }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="{{ route('customer.pesanan.show', $pesanan->id) }}" class="btn btn-sm btn-outline-secondary">
                                                Detail
                                            </a>
                                            @if($pesanan->bolehKonfirmasiDiterima())
                                                <form action="{{ route('customer.pesanan.konfirmasiDiterima', $pesanan->id) }}" method="POST" onsubmit="return confirm('Konfirmasi barang sudah diterima?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Pesanan Diterima</button>
                                                </form>
                                            @elseif($pesanan->bolehDibatalkanCustomer())
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalBatalkan{{ $pesanan->id }}">
                                                    Batalkan
                                                </button>

                                                <div class="modal fade" id="modalBatalkan{{ $pesanan->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('customer.pesanan.batalkan', $pesanan->id) }}" method="POST" class="modal-content">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Batalkan Pesanan {{ $pesanan->kode_transaksi }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <label class="form-label">Alasan pembatalan (wajib diisi, akan dilihat admin)</label>
                                                                <textarea name="alasan_pembatalan" class="form-control" rows="3" required placeholder="Contoh: salah ukuran, berubah pikiran, dll"></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Ya, Batalkan Pesanan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
