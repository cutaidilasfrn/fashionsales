

<?php $__env->startSection('content'); ?>
<div class="container py-4" style="max-width: 700px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-gear"></i> Profil Saya</h4>
        <p class="text-muted mb-0">Kelola data diri dan e-wallet favoritmu untuk mempercepat checkout.</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <label class="form-label text-muted small">Nama</label>
                <input type="text" class="form-control" value="<?php echo e(auth()->user()->name); ?>" disabled>
                <small class="text-muted">Nama akun tidak bisa diubah dari sini.</small>
            </div>

            <form action="<?php echo e(route('customer.profil.update')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="Pria" <?php echo e($pelanggan->jenis_kelamin == 'Pria' ? 'selected' : ''); ?>>Pria</option>
                        <option value="Wanita" <?php echo e($pelanggan->jenis_kelamin == 'Wanita' ? 'selected' : ''); ?>>Wanita</option>
                        <option value="Lainnya" <?php echo e($pelanggan->jenis_kelamin == 'Lainnya' ? 'selected' : ''); ?>>Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="kota" class="form-control" value="<?php echo e(old('kota', $pelanggan->kota)); ?>" placeholder="Kota domisili">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat pengiriman"><?php echo e(old('alamat', $pelanggan->alamat)); ?></textarea>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-wallet2 me-1"></i> E-Wallet Favorit</label>
                    <select name="ewallet_favorit" class="form-select">
                        <option value="">- Belum ada -</option>
                        <?php $__currentLoopData = \App\Models\Pelanggan::EWALLET_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ewallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ewallet); ?>" <?php echo e($pelanggan->ewallet_favorit == $ewallet ? 'selected' : ''); ?>><?php echo e($ewallet); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">
                        E-wallet ini akan otomatis terpilih setiap kamu checkout pakai metode E-Wallet.
                        Kamu tetap bisa ganti providernya per-pesanan.
                    </small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/customer/profil.blade.php ENDPATH**/ ?>