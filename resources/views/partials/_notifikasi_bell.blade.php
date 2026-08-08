{{--
    Dropdown bell notifikasi persisten (tersimpan di DB, beda dari dropdown
    "Pesanan Baru"/"Stok Menipis" yang live-query). Dipakai bareng admin &
    customer — variabel $notifikasiList & $notifikasiBelumDibacaCount
    dikirim dari View::composer di AppServiceProvider, isinya beda otomatis
    tergantung role yang sedang login.
--}}
<div class="dropdown">
    <button class="btn btn-light border position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill text-secondary"></i>
        @if(($notifikasiBelumDibacaCount ?? 0) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $notifikasiBelumDibacaCount }}
                <span class="visually-hidden">notifikasi belum dibaca</span>
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width: 340px;">
        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bell-fill text-secondary me-1"></i> Notifikasi</strong>
            @if(($notifikasiBelumDibacaCount ?? 0) > 0)
                <form action="{{ route('notifikasi.bacaSemua') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <ul class="list-unstyled mb-0" style="max-height: 340px; overflow-y: auto;">
            @forelse(($notifikasiList ?? []) as $notif)
                @php
                    $tujuan = null;
                    if ($notif->transaksi_id) {
                        $tujuan = auth()->user()->role === 'admin'
                            ? route('transaksi.show', $notif->transaksi_id)
                            : route('customer.pesanan.show', $notif->transaksi_id);
                    }
                @endphp
                <li class="px-3 py-2 border-bottom {{ $notif->dibaca_at ? '' : 'bg-light' }}">
                    @if($tujuan)
                        <a href="{{ $tujuan }}" class="d-flex gap-2 text-decoration-none text-dark">
                    @else
                        <div class="d-flex gap-2">
                    @endif
                        <i class="bi {{ $notif->iconClass() }} fs-5 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $notif->judul }}</div>
                            <div class="small text-muted">{{ $notif->pesan }}</div>
                            <div class="small text-muted mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    @if($tujuan)
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @empty
                <li class="px-3 py-4 text-center text-muted small">Belum ada notifikasi.</li>
            @endforelse
        </ul>
    </div>
</div>