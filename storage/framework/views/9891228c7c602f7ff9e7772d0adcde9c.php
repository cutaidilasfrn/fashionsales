
<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e($formAction); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="produk_id" value="<?php echo e($produk->id); ?>">
                <div class="modal-body pt-0">
                    <div class="row g-4">
                        
                        <div class="col-md-5">
                            <?php if($produk->diskon_persen > 0): ?>
                                <span class="badge bg-danger mb-2">Hemat <?php echo e($produk->diskon_persen); ?>%</span>
                            <?php endif; ?>
                            <?php if($produk->gambar): ?>
                                <img src="<?php echo e(asset('storage/' . $produk->gambar)); ?>" class="w-100 rounded-3" style="aspect-ratio: 1 / 1; object-fit: cover;" alt="<?php echo e($produk->nama_produk); ?>">
                            <?php else: ?>
                                <div class="w-100 bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="aspect-ratio: 1 / 1;">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </div>

                        
                        <div class="col-md-7">
                            <h4 class="fw-bold mb-1"><?php echo e($produk->nama_produk); ?></h4>
                            <?php if($produk->material): ?>
                                <p class="text-muted small mb-2">Bahan: <?php echo e($produk->material); ?></p>
                            <?php endif; ?>

                            <div class="mb-3">
                                <?php if($produk->diskon_persen > 0): ?>
                                    <span class="fs-4 fw-bold text-danger">Rp <?php echo e(number_format($produk->harga_promo, 0, ',', '.')); ?></span>
                                    <span class="text-muted text-decoration-line-through ms-2">Rp <?php echo e(number_format($produk->harga_satuan, 0, ',', '.')); ?></span>
                                <?php else: ?>
                                    <span class="fs-4 fw-bold text-primary">Rp <?php echo e(number_format($produk->harga_satuan, 0, ',', '.')); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if($produk->deskripsi): ?>
                                <h6 class="fw-semibold mb-1">Deskripsi Produk</h6>
                                <p class="text-muted small mb-3"><?php echo e($produk->deskripsi); ?></p>
                            <?php endif; ?>

                            
                            <h6 class="fw-semibold mb-2">Pilih Warna</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="radio" class="btn-check" name="warna"
                                           id="<?php echo e($idPrefix); ?>-warna-<?php echo e($i); ?>" value="<?php echo e($warna); ?>"
                                           <?php echo e($i === 0 ? 'checked' : ''); ?> required>
                                    <label class="btn btn-outline-dark rounded-pill px-3" for="<?php echo e($idPrefix); ?>-warna-<?php echo e($i); ?>">
                                        <?php echo e($warna); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            
                            <h6 class="fw-semibold mb-2">Pilih Ukuran</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php $__currentLoopData = ['S', 'M', 'L', 'XL']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $ukuran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="radio" class="btn-check" name="ukuran"
                                           id="<?php echo e($idPrefix); ?>-ukuran-<?php echo e($i); ?>" value="<?php echo e($ukuran); ?>"
                                           <?php echo e($ukuran === 'M' ? 'checked' : ''); ?> required>
                                    <label class="btn btn-outline-dark rounded-pill px-3" for="<?php echo e($idPrefix); ?>-ukuran-<?php echo e($i); ?>">
                                        <?php echo e($ukuran); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            
                            <h6 class="fw-semibold mb-2">Jumlah</h6>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlahModal('<?php echo e($idPrefix); ?>-qty', -1, <?php echo e($produk->stok); ?>)">-</button>
                                <input type="number" name="kuantitas" id="<?php echo e($idPrefix); ?>-qty" class="form-control text-center" style="width: 70px;" value="1" min="1" max="<?php echo e($produk->stok); ?>" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlahModal('<?php echo e($idPrefix); ?>-qty', 1, <?php echo e($produk->stok); ?>)">+</button>
                                <small class="text-muted ms-2">Sisa stok: <?php echo e($produk->stok); ?></small>
                            </div>

                            <?php if($showPembayaran): ?>
                                <h6 class="fw-semibold mb-2">Metode Pembayaran</h6>
                                <div class="mb-3">
                                    <select name="metode_pembayaran" class="form-select" required onchange="toggleEwalletModal(this, '<?php echo e($idPrefix); ?>')">
                                        <option value="Transfer Bank">Transfer Bank</option>
                                        <option value="E-Wallet">E-Wallet</option>
                                        <option value="COD">COD (Bayar di Tempat)</option>
                                    </select>
                                </div>
                                <div class="mb-3 ewallet-provider-field" id="<?php echo e($idPrefix); ?>-ewallet-field" style="display:none;">
                                    <label class="form-label">Pilih E-Wallet</label>
                                    <select name="ewallet_provider" class="form-select">
                                        <?php $__currentLoopData = \App\Models\Pelanggan::EWALLET_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ewallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($ewallet); ?>" <?php echo e($ewalletFavorit === $ewallet ? 'selected' : ''); ?>><?php echo e($ewallet); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php if($ewalletFavorit): ?>
                                        <small class="text-muted">Otomatis dipilih e-wallet favoritmu, bisa diganti kapan saja.</small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn <?php echo e($submitClass); ?> flex-fill"><?php echo e($submitLabel); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/customer/partials/_produk_modal.blade.php ENDPATH**/ ?>