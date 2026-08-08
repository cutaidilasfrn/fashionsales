

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Data Transaksi</h2>
            <p class="text-muted mb-0">Daftar seluruh transaksi penjualan</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Kode Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Metode Bayar</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $transaksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4"><?php echo e($transaksis->firstItem() + $index); ?></td>
                                <td class="fw-bold text-primary"><?php echo e($t->kode_transaksi); ?></td>
                                <td><?php echo e($t->nama_pelanggan ?? $t->pelanggan->nama_pelanggan ?? '-'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($t->tanggal_transaksi)->format('d M Y H:i')); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($t->metode_pembayaran); ?></span></td>
                                <td class="fw-bold">Rp <?php echo e(number_format($t->grand_total, 0, ',', '.')); ?></td>
                                <td>
                                    <!-- Form Ubah Status Langsung oleh Admin -->
                                    <form action="<?php echo e(route('transaksi.updateStatus', $t->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <select name="status_pesanan" onchange="this.form.submit()" class="form-select form-select-sm border-0 fw-semibold 
                                            <?php if($t->status_pesanan == 'Selesai'): ?> text-success bg-success-subtle
                                            <?php elseif($t->status_pesanan == 'Batal'): ?> text-danger bg-danger-subtle
                                            <?php elseif($t->status_pesanan == 'Dikirim'): ?> text-info bg-info-subtle
                                            <?php else: ?> text-warning bg-warning-subtle <?php endif; ?>">
                                            <option value="Pending" <?php echo e($t->status_pesanan == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                            <option value="Proses" <?php echo e($t->status_pesanan == 'Proses' ? 'selected' : ''); ?>>Proses</option>
                                            <option value="Dikirim" <?php echo e($t->status_pesanan == 'Dikirim' ? 'selected' : ''); ?>>Dikirim</option>
                                            <option value="Selesai" <?php echo e($t->status_pesanan == 'Selesai' ? 'selected' : ''); ?>>Selesai</option>
                                            <option value="Batal" <?php echo e($t->status_pesanan == 'Batal' ? 'selected' : ''); ?>>Batal</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="<?php echo e(route('transaksi.show', $t->id)); ?>" class="btn btn-sm btn-outline-primary rounded-2">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <?php echo e($transaksis->links('pagination::bootstrap-5')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/transaksi/index.blade.php ENDPATH**/ ?>