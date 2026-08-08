

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Detail Pesanan</h2>
        <small class="text-muted"><?php echo e($pesanan->kode_transaksi); ?></small>
    </div>
    <div class="d-flex gap-2">
        <?php if($pesanan->bolehKonfirmasiDiterima()): ?>
            <form action="<?php echo e(route('customer.pesanan.konfirmasiDiterima', $pesanan->id)); ?>" method="POST" onsubmit="return confirm('Konfirmasi barang sudah diterima?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Pesanan Diterima</button>
            </form>
        <?php elseif($pesanan->bolehDibatalkanCustomer()): ?>
            <form action="<?php echo e(route('customer.pesanan.batalkan', $pesanan->id)); ?>" method="POST" onsubmit="return confirm('Yakin batalkan pesanan ini?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i> Batalkan Pesanan</button>
            </form>
        <?php endif; ?>
        <a href="<?php echo e(route('customer.dashboard')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<?php if($errors->has('batal') || $errors->has('status')): ?>
    <div class="alert alert-danger"><?php echo e($errors->first('batal') ?? $errors->first('status')); ?></div>
<?php endif; ?>

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
                    <tr><th>Status Pembayaran</th><td><span class="badge <?php echo e($pesanan->badgePembayaranClass()); ?>"><?php echo e($pesanan->status_pembayaran); ?></span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="180">Status Pesanan</th>
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

<?php
    $isTransferBank = str_starts_with($pesanan->metode_pembayaran, 'Transfer Bank');
    $isEwallet = str_starts_with($pesanan->metode_pembayaran, 'E-Wallet');
?>
<?php if($isTransferBank || $isEwallet): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-upload text-success me-2"></i>Pembayaran
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if($errors->has('bukti_pembayaran')): ?>
                <div class="alert alert-danger"><?php echo e($errors->first('bukti_pembayaran')); ?></div>
            <?php endif; ?>

            <?php $sp = $pesanan->status_pembayaran; ?>

            <?php if($sp === \App\Models\Transaksi::STATUS_LUNAS): ?>
                <p class="text-success mb-0"><i class="bi bi-check-circle-fill me-1"></i> Pembayaran sudah dikonfirmasi. Pesananmu akan segera diproses.</p>

            <?php elseif($sp === \App\Models\Transaksi::STATUS_MENUNGGU_VERIFIKASI): ?>
                <p class="text-muted mb-2">Bukti pembayaran sudah diunggah, sedang menunggu diperiksa admin.</p>
                <?php if($pesanan->bukti_pembayaran): ?>
                    <img src="<?php echo e(asset('storage/' . $pesanan->bukti_pembayaran)); ?>" alt="Bukti pembayaran" class="rounded border" style="max-width: 240px;">
                <?php endif; ?>

            <?php else: ?>
                
                <?php if($sp === \App\Models\Transaksi::STATUS_DITOLAK): ?>
                    <div class="alert alert-danger">
                        Bukti pembayaran sebelumnya ditolak<?php echo e($pesanan->catatan_pembayaran ? ': ' . $pesanan->catatan_pembayaran : '.'); ?> Silakan unggah ulang bukti yang benar.
                    </div>
                <?php else: ?>
                    <p class="text-muted">
                        Segera bayar sejumlah <strong>Rp <?php echo e(number_format($pesanan->grand_total,0,',','.')); ?></strong>
                        ke <?php echo e($isEwallet ? 'nomor e-wallet' : 'salah satu tujuan'); ?> di bawah, lalu unggah bukti pembayarannya.
                    </p>
                <?php endif; ?>

                <?php if($isTransferBank): ?>
                    <?php
                        $bank = config('pembayaran.bank');
                        $qrisImage = config('pembayaran.qris_image');
                        $qrisAda = $qrisImage && file_exists(public_path($qrisImage));
                    ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-<?php echo e($qrisAda ? 6 : 12); ?>">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-muted small mb-2 text-uppercase fw-semibold">Transfer Bank</p>
                                <p class="mb-1"><strong><?php echo e($bank['nama_bank']); ?></strong></p>
                                <p class="mb-1 fs-5 fw-bold text-primary" style="letter-spacing: 1px;">
                                    <?php echo e($bank['no_rekening']); ?>

                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1"
                                            onclick="navigator.clipboard.writeText('<?php echo e($bank['no_rekening']); ?>'); this.innerText='Tersalin!'; setTimeout(() => this.innerText='Salin', 1500);">
                                        Salin
                                    </button>
                                </p>
                                <p class="text-muted mb-0">a.n. <?php echo e($bank['atas_nama']); ?></p>
                            </div>
                        </div>
                        <?php if($qrisAda): ?>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 text-center">
                                    <p class="text-muted small mb-2 text-uppercase fw-semibold">QRIS</p>
                                    <img src="<?php echo e(asset($qrisImage)); ?>" alt="QRIS" style="max-width: 200px; width: 100%;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php $ewallet = config('pembayaran.ewallet'); ?>
                    <?php
                        // Ambil nama provider dari "E-Wallet - OVO" -> "OVO"
                        $provider = trim(str_replace('E-Wallet -', '', $pesanan->metode_pembayaran));
                    ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-muted small mb-2 text-uppercase fw-semibold"><?php echo e($provider ?: 'E-Wallet'); ?></p>
                                <p class="mb-1 fs-5 fw-bold text-primary" style="letter-spacing: 1px;">
                                    <?php echo e($ewallet['nomor_tujuan']); ?>

                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1"
                                            onclick="navigator.clipboard.writeText('<?php echo e($ewallet['nomor_tujuan']); ?>'); this.innerText='Tersalin!'; setTimeout(() => this.innerText='Salin', 1500);">
                                        Salin
                                    </button>
                                </p>
                                <p class="text-muted mb-0">a.n. <?php echo e($ewallet['atas_nama']); ?></p>
                                <p class="text-muted small mb-0 mt-2">Kirim ke nomor di atas lewat aplikasi <?php echo e($provider ?: 'e-wallet'); ?> kamu, lalu screenshot bukti transaksinya.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('customer.pesanan.uploadBukti', $pesanan->id)); ?>" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-start flex-wrap">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="bukti_pembayaran" accept="image/*" class="form-control" style="max-width: 320px;" required>
                    <button type="submit" class="btn btn-success">Unggah Bukti Pembayaran</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

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
                        <th>Warna</th>
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
                        <td><?php echo e($detail->warna ?? '-'); ?></td>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales-new\resources\views/customer/pesanan_show.blade.php ENDPATH**/ ?>