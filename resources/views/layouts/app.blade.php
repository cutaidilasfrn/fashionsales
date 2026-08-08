<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Fashion Sales Management System' }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #212529; /* Dark Sidebar */
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar .brand-title {
            padding: 20px 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-bottom: 1px solid #343a40;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd; /* Primary Active Color */
            border-radius: 6px;
            margin: 0 10px;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        /* Styling jika tidak login (Login/Register) agar posisi di tengah */
        .main-content.full-width {
            margin-left: 0 !important;
            padding: 15px;
        }

        /* Responsive Breakpoint untuk Layar Kecil */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    @auth
        <!-- SIDEBAR KIRI (HANYA MUNCUL JIKA USER SUDAH LOGIN) -->
        <aside class="sidebar d-flex flex-column justify-content-between p-2">
            <div>
                <!-- Brand Logo / Name -->
                <a href="#" class="brand-title">
                    <i class="bi bi-bag-heart-fill text-primary fs-4"></i>
                    <span>Fashion Sales</span>
                </a>

                <!-- Menu Navigasi Sesuai Role -->
                <ul class="nav nav-pills flex-column mt-3">
                    @if(auth()->user()->role === 'admin')
                        <!-- Menu Admin -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('transaksi.index') }}" class="nav-link d-flex align-items-center justify-content-between {{ request()->is('transaksi*') ? 'active' : '' }}">
                                <span><i class="bi bi-receipt"></i> <span>Transaksi</span></span>
                                @php
                                    $jumlahPerluVerifikasi = \App\Models\Transaksi::where('status_pembayaran', \App\Models\Transaksi::STATUS_MENUNGGU_VERIFIKASI)->count();
                                @endphp
                                @if($jumlahPerluVerifikasi > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $jumlahPerluVerifikasi }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('produk.index') }}" class="nav-link {{ request()->is('produk*') ? 'active' : '' }}">
                                <i class="bi bi-box-seam"></i>
                                <span>Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('pelanggan.index') }}" class="nav-link {{ request()->is('pelanggan*') ? 'active' : '' }}">
                                <i class="bi bi-people"></i>
                                <span>Pelanggan</span>
                            </a>
                        </li>
                    @else
                        <!-- Menu Customer -->
                        <li class="nav-item">
                            <a href="{{ route('customer.katalog') }}" class="nav-link {{ request()->is('customer/katalog*') ? 'active' : '' }}">
                                <i class="bi bi-grid"></i>
                                <span>Katalog Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customer.promo') }}" class="nav-link {{ request()->is('customer/promo*') ? 'active' : '' }}">
                                <i class="bi bi-tags"></i>
                                <span>Promo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customer.keranjang.index') }}" class="nav-link d-flex align-items-center justify-content-between {{ request()->is('keranjang*') ? 'active' : '' }}">
                                <span><i class="bi bi-cart3"></i> <span>Keranjang</span></span>
                                @if(($jumlahKeranjang ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $jumlahKeranjang }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customer.pesanan.index') }}" class="nav-link {{ request()->is('pesanan-saya*') ? 'active' : '' }}">
                                <i class="bi bi-bag-check"></i>
                                <span>Pesanan Saya</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customer.profil.edit') }}" class="nav-link {{ request()->is('profil*') ? 'active' : '' }}">
                                <i class="bi bi-person-gear"></i>
                                <span>Profil Saya</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- User Profile Info & Tombol Logout Bagian Bawah Sidebar -->
            <div class="border-top border-secondary pt-3 px-2 mb-2">
                <div class="d-flex align-items-center justify-content-between text-white mb-2">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <i class="bi bi-person-circle fs-4"></i>
                        <small class="fw-semibold text-truncate" style="max-width: 130px;">{{ auth()->user()->name }}</small>
                    </div>
                    <span class="badge bg-primary text-uppercase">{{ auth()->user()->role }}</span>
                </div>

                <!-- Form Logout -->
                <form action="{{ route('logout') }}" method="POST" class="w-100 mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>
    @endauth

    <!-- CONTENT UTAMA -->
    <main class="main-content @guest full-width @endguest">
        @auth
        @php
            $adaTopbarAdmin = auth()->user()->role === 'admin'
                && ((($stokMenipisList ?? collect())->count() > 0) || (($pesananBaruList ?? collect())->count() > 0));
            $adaTopbar = $adaTopbarAdmin || isset($notifikasiList);
        @endphp
        @if($adaTopbar)
                <nav class="d-flex justify-content-end gap-2 px-3 pt-3">
                    @if(auth()->user()->role === 'admin' && isset($pesananBaruList) && $pesananBaruList->count() > 0)
                        <div class="dropdown">
                            <button class="btn btn-light border position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bag-check-fill text-success"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $pesananBaruList->count() }}
                                    <span class="visually-hidden">pesanan baru</span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width: 320px;">
                                <div class="px-3 py-2 border-bottom bg-light">
                                    <strong><i class="bi bi-bag-check-fill text-success me-1"></i> Pesanan Baru</strong>
                                    <div class="small text-muted">{{ $pesananBaruList->count() }} pesanan berstatus Pending</div>
                                </div>
                                <ul class="list-unstyled mb-0" style="max-height: 280px; overflow-y: auto;">
                                    @foreach($pesananBaruList as $p)
                                        <li class="px-3 py-2 border-bottom">
                                            <a href="{{ route('transaksi.show', $p->id) }}" class="d-flex justify-content-between align-items-center text-decoration-none text-dark">
                                                <span>
                                                    <span class="d-block fw-semibold">{{ $p->kode_transaksi }}</span>
                                                    <span class="d-block small text-muted">{{ $p->nama_pelanggan }}</span>
                                                </span>
                                                <span class="badge bg-warning text-dark">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('transaksi.index') }}" class="d-block text-center py-2 small fw-semibold text-decoration-none border-top">
                                    Kelola Transaksi &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->role === 'admin' && isset($stokMenipisList) && $stokMenipisList->count() > 0)
                    <div class="dropdown">
                        <button class="btn btn-light border position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill text-warning"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $stokMenipisList->count() }}
                                <span class="visually-hidden">produk stok menipis</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width: 320px;">
                            <div class="px-3 py-2 border-bottom bg-light">
                                <strong><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Stok Menipis</strong>
                                <div class="small text-muted">{{ $stokMenipisList->count() }} produk sudah &le; {{ \App\Models\Produk::BATAS_STOK_MENIPIS }} unit</div>
                            </div>
                            <ul class="list-unstyled mb-0" style="max-height: 320px; overflow-y: auto;">
                                @foreach($stokMenipisList as $variasiSatuProduk)
                                    @php $produkTerkait = $variasiSatuProduk->first()->produk; @endphp
                                    <li class="px-3 py-2 border-bottom">
                                        <div class="fw-semibold text-truncate mb-1">{{ $produkTerkait->nama_produk }}</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($variasiSatuProduk->sortBy('stok') as $v)
                                                <span class="badge {{ $v->stok == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                    {{ $v->warna }} &middot; {{ $v->ukuran }}: {{ $v->stok == 0 ? 'Habis' : $v->stok . ' tersisa' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('produk.index') }}" class="d-block text-center py-2 small fw-semibold text-decoration-none border-top">
                                Kelola Stok Produk &rarr;
                            </a>
                        </div>
                    </div>
                    @endif

                    @include('partials._notifikasi_bell')
                </nav>
            @endif
        @endauth
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>