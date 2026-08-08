

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Detail Transaksi</h2>
            <small class="text-muted"><?php echo e($transaksi->kode_transaksi); ?></small>
        </div>
        <a href="<?php echo e(route('transaksi.index')); ?>" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white">Informasi Transaksi</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th width="180">Pelanggan</th><td><?php echo e($transaksi->nama_pelanggan ?? $transaksi->pelanggan->nama_pelanggan ?? '-'); ?></td></tr>
                        <tr><th>Jenis Kelamin</th><td><?php echo e($transaksi->jenis_kelamin ?? $transaksi->pelanggan->jenis_kelamin ?? '-'); ?></td></tr>
                        <tr><th>Kota</th><td><?php echo e($transaksi->kota ?? $transaksi->pelanggan->kota ?? '-'); ?></td></tr>
                        <tr><th>Alamat</th><td><?php echo e($transaksi->alamat ?? '-'); ?></td></tr>
                        <tr><th>Platform</th><td><?php echo e($transaksi->platform->nama_platform); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th width="180">Tanggal</th><td><?php echo e(\Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d F Y H:i')); ?></td></tr>
                        <tr><th>Metode Pembayaran</th><td><?php echo e($transaksi->metode_pembayaran); ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-success"><?php echo e($transaksi->status_pesanan); ?></span></td></tr>
                        <tr><th>Grand Total</th><td class="fw-bold text-success">Rp <?php echo e(number_format($transaksi->grand_total,0,',','.')); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">Produk yang Dibeli</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produk</th>
                            <th>Material</th>
                            <th>Ukuran</th>
                            <th>Warna</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transaksi->detailTransaksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($detail->produk->nama_produk); ?></td>
                            <td><?php echo e($detail->produk->material); ?></td>
                            <td><?php echo e($detail->ukuran); ?></td>
                            <td><?php echo e($detail->warna ?? '-'); ?></td>
                            <td><?php echo e($detail->kuantitas); ?></td>
                            <td>Rp <?php echo e(number_format($detail->harga_satuan,0,',','.')); ?></td>
                            <td><?php echo e($detail->diskon); ?>%</td>
                            <td class="text-end">Rp <?php echo e(number_format($detail->subtotal,0,',','.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/transaksi/show.blade.php ENDPATH**/ ?>