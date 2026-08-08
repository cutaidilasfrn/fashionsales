

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
                        <tr><th>Status Pembayaran</th><td><span class="badge <?php echo e($transaksi->badgePembayaranClass()); ?>"><?php echo e($transaksi->status_pembayaran); ?></span></td></tr>
                        <tr><th>Status Pesanan</th><td><span class="badge <?php echo e($transaksi->status_pesanan === 'Batal' ? 'bg-danger' : 'bg-success'); ?>"><?php echo e($transaksi->status_pesanan); ?></span></td></tr>
                        <tr><th>Grand Total</th><td class="fw-bold text-success">Rp <?php echo e(number_format($transaksi->grand_total,0,',','.')); ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if($transaksi->status_pesanan === 'Batal'): ?>
                <div class="alert alert-danger mt-3 mb-0">
                    <strong>Pesanan dibatalkan oleh <?php echo e(match($transaksi->dibatalkan_oleh) {
                        'customer' => 'customer',
                        'admin' => 'admin',
                        default => 'sistem (kadaluarsa, tidak dibayar dalam 1x24 jam)',
                    }); ?>.</strong>
                    <?php if($transaksi->alasan_pembatalan): ?>
                        <div class="mt-1">Alasan: <?php echo e($transaksi->alasan_pembatalan); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(str_starts_with($transaksi->metode_pembayaran, 'Transfer Bank') || str_starts_with($transaksi->metode_pembayaran, 'E-Wallet')): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Verifikasi Pembayaran</div>
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <?php if($transaksi->bukti_pembayaran): ?>
                    <p class="text-muted mb-2">Bukti pembayaran yang diunggah pelanggan:</p>
                    <img src="<?php echo e(asset('storage/' . $transaksi->bukti_pembayaran)); ?>" alt="Bukti pembayaran"
                         class="rounded border mb-3" style="max-width: 320px; max-height: 320px; object-fit: contain;">
                <?php else: ?>
                    <p class="text-muted mb-3">Pelanggan belum mengunggah bukti pembayaran.</p>
                <?php endif; ?>

                <?php if($transaksi->status_pembayaran === \App\Models\Transaksi::STATUS_DITOLAK && $transaksi->catatan_pembayaran): ?>
                    <p class="text-danger mb-3"><strong>Alasan ditolak:</strong> <?php echo e($transaksi->catatan_pembayaran); ?></p>
                <?php endif; ?>

                <?php if($transaksi->status_pembayaran === \App\Models\Transaksi::STATUS_MENUNGGU_VERIFIKASI): ?>
                    <div class="d-flex gap-2">
                        <form action="<?php echo e(route('transaksi.updateStatusPembayaran', $transaksi->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status_pembayaran" value="Lunas">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Konfirmasi Lunas
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalTolak">
                            <i class="bi bi-x-circle me-1"></i> Tolak
                        </button>
                    </div>

                    <!-- Modal alasan penolakan -->
                    <div class="modal fade" id="modalTolak" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="<?php echo e(route('transaksi.updateStatusPembayaran', $transaksi->id)); ?>" method="POST" class="modal-content">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input type="hidden" name="status_pembayaran" value="Ditolak">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tolak Bukti Transfer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">Alasan (opsional, akan dilihat pelanggan)</label>
                                    <input type="text" name="catatan_pembayaran" class="form-control" placeholder="Contoh: nominal tidak sesuai">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif($transaksi->status_pembayaran === \App\Models\Transaksi::STATUS_LUNAS): ?>
                    <p class="text-success mb-0"><i class="bi bi-check-circle-fill me-1"></i> Pembayaran sudah dikonfirmasi lunas.</p>
                <?php elseif($transaksi->status_pembayaran === \App\Models\Transaksi::STATUS_MENUNGGU_PEMBAYARAN): ?>
                    <p class="text-muted mb-0">Menunggu pelanggan mengunggah bukti pembayaran.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\DELL\Downloads\fashion-sales-new (2)\fashion-sales-terbaru\resources\views/transaksi/show.blade.php ENDPATH**/ ?>