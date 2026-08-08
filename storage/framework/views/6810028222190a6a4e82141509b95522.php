

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Detail Pesanan</h2>
        <small class="text-muted"><?php echo e($pesanan->kode_transaksi); ?></small>
    </div>
    <a href="<?php echo e(route('customer.dashboard')); ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold py-3">
        <i class="bi bi-info-circle text-success me-2"></i>Informasi Pesanan
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr><th width="180">Tanggal</th><td><?php echo e(\Carbon\Carbon::parse($pesanan->tanggal_transaksi)->format('d F Y H:i')); ?></td></tr>
                    <tr><th>Platform</th><td><?php echo e($pesanan->platform->nama_platform ?? '-'); ?></td></tr>
                    <tr><th>Metode Pembayaran</th><td><?php echo e($pesanan->metode_pembayaran); ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="180">Status</th>
                        <td>
                            <?php $status = strtolower($pesanan->status_pesanan); ?>
                            <?php if($status == 'selesai'): ?>
                                <span class="badge bg-success"><?php echo e($pesanan->status_pesanan); ?></span>
                            <?php elseif($status == 'pending'): ?>
                                <span class="badge bg-warning text-dark"><?php echo e($pesanan->status_pesanan); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo e($pesanan->status_pesanan); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Grand Total</th><td class="fw-bold text-success">Rp <?php echo e(number_format($pesanan->grand_total,0,',','.')); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <i class="bi bi-bag-check text-success me-2"></i>Produk yang Dipesan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Material</th>
                        <th>Ukuran</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $pesanan->detailTransaksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($detail->produk->nama_produk ?? '-'); ?></td>
                        <td><?php echo e($detail->produk->material ?? '-'); ?></td>
                        <td><?php echo e($detail->ukuran); ?></td>
                        <td class="text-center"><?php echo e($detail->kuantitas); ?></td>
                        <td class="text-end">Rp <?php echo e(number_format($detail->harga_satuan,0,',','.')); ?></td>
                        <td class="text-end">Rp <?php echo e(number_format($detail->subtotal,0,',','.')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Grand Total</th>
                        <th class="text-end text-success">Rp <?php echo e(number_format($pesanan->grand_total,0,',','.')); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/customer/pesanan_show.blade.php ENDPATH**/ ?>