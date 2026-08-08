

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Katalog Produk Fashion</h4>
        <a href="<?php echo e(route('customer.pesanan.index')); ?>" class="btn btn-outline-secondary">Riwayat Pesanan Saya</a>
    </div>

    <div class="row">
        <?php $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <?php if($produk->gambar): ?>
                        <img src="<?php echo e(asset('storage/' . $produk->gambar)); ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?php echo e($produk->nama_produk); ?>">
                    <?php else: ?>
                        <div class="bg-light text-center py-5 text-muted">No Image</div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <?php if($produk->diskon_persen > 0): ?>
                            <span class="badge bg-danger mb-1" style="width: fit-content;">Promo -<?php echo e($produk->diskon_persen); ?>%</span>
                        <?php endif; ?>
                        <h6 class="card-title fw-bold"><?php echo e($produk->nama_produk); ?></h6>
                        <p class="card-text text-muted mb-1"><small>Bahan: <?php echo e($produk->material ?? '-'); ?></small></p>
                        <?php if($produk->deskripsi): ?>
                            <p class="card-text text-muted mb-1"><small><?php echo e(\Illuminate\Support\Str::limit($produk->deskripsi, 70)); ?></small></p>
                        <?php endif; ?>

                        <?php if($produk->diskon_persen > 0): ?>
                            <div class="mt-auto">
                                <small class="text-muted text-decoration-line-through">Rp <?php echo e(number_format($produk->harga_satuan, 0, ',', '.')); ?></small>
                                <h6 class="text-danger mb-0">Rp <?php echo e(number_format($produk->harga_promo, 0, ',', '.')); ?></h6>
                            </div>
                        <?php else: ?>
                            <h6 class="text-primary mt-auto">Rp <?php echo e(number_format($produk->harga_satuan, 0, ',', '.')); ?></h6>
                        <?php endif; ?>

                        <?php if($produk->stok <= 0): ?>
                            <small class="text-danger">Stok habis</small>
                        <?php else: ?>
                            <small class="text-muted">Sisa stok: <?php echo e($produk->stok); ?></small>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#cartModal<?php echo e($produk->id); ?>" <?php echo e($produk->stok <= 0 ? 'disabled' : ''); ?>>
                                <i class="bi bi-cart-plus"></i> Keranjang
                            </button>
                            <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo e($produk->id); ?>" <?php echo e($produk->stok <= 0 ? 'disabled' : ''); ?>>
                                Beli Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $__env->make('customer.partials._produk_modal', [
                'produk' => $produk,
                'modalId' => 'orderModal' . $produk->id,
                'idPrefix' => 'katalog-beli-' . $produk->id,
                'formAction' => route('customer.checkout'),
                'submitLabel' => 'Konfirmasi Order',
                'submitClass' => 'btn-success',
                'showPembayaran' => true,
                'ewalletFavorit' => $ewalletFavorit,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php echo $__env->make('customer.partials._produk_modal', [
                'produk' => $produk,
                'modalId' => 'cartModal' . $produk->id,
                'idPrefix' => 'katalog-cart-' . $produk->id,
                'formAction' => route('customer.keranjang.store'),
                'submitLabel' => 'Tambah ke Keranjang',
                'submitClass' => 'btn-primary',
                'showPembayaran' => false,
                'ewalletFavorit' => null,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<script>
    // Tombol +/- kuantitas di modal produk (dipakai semua modal beli & keranjang)
    function ubahJumlahModal(inputId, delta, maxStok) {
        const input = document.getElementById(inputId);
        let nilai = (parseInt(input.value, 10) || 1) + delta;
        if (nilai < 1) nilai = 1;
        if (nilai > maxStok) nilai = maxStok;
        input.value = nilai;
    }

    // Tampilkan/sembunyikan pilihan provider e-wallet di modal Beli Sekarang
    function toggleEwalletModal(select, idPrefix) {
        const field = document.getElementById(idPrefix + '-ewallet-field');
        const ewalletSelect = field.querySelector('select[name="ewallet_provider"]');
        if (select.value === 'E-Wallet') {
            field.style.display = 'block';
            ewalletSelect.required = true;
        } else {
            field.style.display = 'none';
            ewalletSelect.required = false;
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru\resources\views/customer/katalog.blade.php ENDPATH**/ ?>