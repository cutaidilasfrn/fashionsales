

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-cart3"></i> Keranjang Saya</h4>
        <a href="<?php echo e(route('customer.katalog')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if($items->isEmpty()): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                Keranjang kamu masih kosong. Yuk pilih produk dulu di katalog.
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran</th>
                                <th>Warna</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if($item->produk->gambar): ?>
                                                <img src="<?php echo e(asset('storage/' . $item->produk->gambar)); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" alt="<?php echo e($item->produk->nama_produk); ?>">
                                            <?php endif; ?>
                                            <span><?php echo e($item->produk->nama_produk); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo e($item->ukuran); ?></td>
                                    <td><?php echo e($item->warna); ?></td>
                                    <td class="text-center" style="max-width: 130px;">
                                        <form action="<?php echo e(route('customer.keranjang.update', $item->id)); ?>" method="POST" class="d-flex align-items-center gap-1">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <input type="number" name="kuantitas" value="<?php echo e($item->kuantitas); ?>" min="1" max="<?php echo e($item->produk->stok); ?>" class="form-control form-control-sm" style="width: 70px;">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Perbarui jumlah">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">Rp <?php echo e(number_format($item->harga_satuan, 0, ',', '.')); ?></td>
                                    <td class="text-end fw-semibold">Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></td>
                                    <td class="text-center">
                                        <form action="<?php echo e(route('customer.keranjang.destroy', $item->id)); ?>" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Produk</span>
                            <span>Rp <?php echo e(number_format($grandTotalProduk, 0, ',', '.')); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Biaya Pengiriman</span>
                            <span>Rp <?php echo e(number_format($biayaPengiriman, 0, ',', '.')); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Grand Total</span>
                            <span class="fw-bold text-primary fs-5">Rp <?php echo e(number_format($grandTotalProduk + $biayaPengiriman, 0, ',', '.')); ?></span>
                        </div>

                        <form action="<?php echo e(route('customer.keranjang.checkout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select" required onchange="toggleKeranjangEwallet(this)">
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                    <option value="COD">COD (Bayar di Tempat)</option>
                                </select>
                            </div>
                            <div class="mb-3 ewallet-provider-field" id="keranjangEwalletField" style="display:none;">
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
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-bag-check"></i> Checkout Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleKeranjangEwallet(select) {
        const field = document.getElementById('keranjangEwalletField');
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/customer/keranjang.blade.php ENDPATH**/ ?>