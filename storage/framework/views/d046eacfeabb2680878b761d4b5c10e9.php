

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="mb-4">
        <h2 class="fw-bold">Dashboard Fashion Sales</h2>
        <p class="text-muted">Ringkasan data penjualan Fashion Sales Management System</p>
    </div>

    <!-- 1. STAT CARDS -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Pendapatan</h6>
                    <h3 class="fw-bold text-success">Rp <?php echo e(number_format($totalPendapatan,0,',','.')); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Transaksi</h6>
                    <h3 class="fw-bold"><?php echo e(number_format($totalTransaksi)); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Produk</h6>
                    <h3 class="fw-bold"><?php echo e(number_format($totalProduk)); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Pelanggan</h6>
                    <h3 class="fw-bold"><?php echo e(number_format($totalPelanggan)); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. LINE CHART (TREN PENDAPATAN BULANAN) -->
    <div class="row mt-2 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0 text-primary">
                        <i class="bi bi-graph-up-arrow me-2"></i>Tren Pendapatan Bulanan (<?php echo e(date('Y')); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="lineChartPenjualan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. PRODUK TERLARIS & DOUGHNUT CHART PLATFORM -->
    <div class="row">
        <!-- Tabel Produk Terlaris -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-primary text-white fw-semibold">Produk Terlaris</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Produk</th>
                                <th class="text-end px-3">Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $produkTerlaris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-3"><?php echo e($produk->nama_produk); ?></td>
                                    <td class="text-end px-3">
                                        <span class="badge bg-success rounded-pill"><?php echo e($produk->total); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-3">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grafik Platform -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-dark text-white fw-semibold">Platform Penjualan</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="height: 250px; width: 100%;">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. SCRIPT DOUGHNUT CHART PLATFORM ---
        const platformCtx = document.getElementById('platformChart');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php $__currentLoopData = $platformTerlaris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        '<?php echo e($item->nama_platform); ?>',
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ],
                datasets: [{
                    data: [
                        <?php $__currentLoopData = $platformTerlaris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($item->total); ?>,
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // --- 2. SCRIPT LINE CHART TREN PENDAPATAN ---
        const lineCtx = document.getElementById('lineChartPenjualan').getContext('2d');
        
        // Efek gradien di bawah garis
        const gradient = lineCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.3)');
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?php echo json_encode($chartData); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru\resources\views/transaksi/dashboard.blade.php ENDPATH**/ ?>