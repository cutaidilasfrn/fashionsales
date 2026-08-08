

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Riwayat Pesanan Saya</h4>
        <a href="<?php echo e(route('customer.katalog')); ?>" class="btn btn-primary">
            <i class="bi bi-grid me-1"></i> Belanja Lagi
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if($pesanans->isEmpty()): ?>
                <p class="text-muted text-center py-4 mb-0">Kamu belum punya pesanan. Yuk mulai belanja!</p>
            <?php else: ?>
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
                            <?php $__currentLoopData = $pesanans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pesanan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $status = strtolower($pesanan->status_pesanan); ?>
                                <tr>
                                    <td><?php echo e($pesanan->kode_transaksi); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($pesanan->tanggal_transaksi)->format('d M Y H:i')); ?></td>
                                    <td><?php echo e($pesanan->platform->nama_platform ?? '-'); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($pesanan->grand_total, 0, ',', '.')); ?></td>
                                    <td class="text-center">
                                        <?php if($status == 'selesai'): ?>
                                            <span class="badge bg-success"><?php echo e($pesanan->status_pesanan); ?></span>
                                        <?php elseif($status == 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><?php echo e($pesanan->status_pesanan); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo e($pesanan->status_pesanan); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="<?php echo e(route('customer.pesanan.show', $pesanan->id)); ?>" class="btn btn-sm btn-outline-secondary">
                                                Detail
                                            </a>
                                            <?php if($pesanan->bolehKonfirmasiDiterima()): ?>
                                                <form action="<?php echo e(route('customer.pesanan.konfirmasiDiterima', $pesanan->id)); ?>" method="POST" onsubmit="return confirm('Konfirmasi barang sudah diterima?');">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm btn-success">Pesanan Diterima</button>
                                                </form>
                                            <?php elseif($pesanan->bolehDibatalkanCustomer()): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalBatalkan<?php echo e($pesanan->id); ?>">
                                                    Batalkan
                                                </button>

                                                <div class="modal fade" id="modalBatalkan<?php echo e($pesanan->id); ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <form action="<?php echo e(route('customer.pesanan.batalkan', $pesanan->id)); ?>" method="POST" class="modal-content">
                                                            <?php echo csrf_field(); ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Batalkan Pesanan <?php echo e($pesanan->kode_transaksi); ?></h5>
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
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru-fixed\resources\views/customer/pesanan_index.blade.php ENDPATH**/ ?>