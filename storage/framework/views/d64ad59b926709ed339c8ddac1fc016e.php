

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Edit Produk: <?php echo e($produk->nama_produk); ?></h3>
        <a href="<?php echo e(route('produk.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi me-1"></i> Kembali
        </a>
    </div>

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

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="<?php echo e(route('produk.update', $produk->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <h5 class="fw-bold mb-3 text-primary">Informasi Produk</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control" value="<?php echo e(old('nama_produk', $produk->nama_produk)); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga_satuan" class="form-control" value="<?php echo e(old('harga_satuan', $produk->harga_satuan)); ?>" min="0" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Diskon Promo (%)</label>
                        <input type="number" name="diskon_persen" class="form-control" value="<?php echo e(old('diskon_persen', $produk->diskon_persen)); ?>" min="0" max="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bahan / Material</label>
                        <input type="text" name="material" class="form-control" value="<?php echo e(old('material', $produk->material)); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ganti Gambar Utama (Cover/Thumbnail)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <?php if($produk->gambar): ?>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="<?php echo e(asset('storage/' . $produk->gambar)); ?>" class="rounded border" style="height: 50px; width: 50px; object-fit: cover;">
                                <span class="text-muted small">Gambar utama saat ini</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?php echo e(old('deskripsi', $produk->deskripsi)); ?></textarea>
                    </div>
                </div>

                <hr class="my-4">

                
                <h5 class="fw-bold mb-2 text-primary">Upload Gambar Khusus Per Warna</h5>
                <p class="text-muted small mb-3">Pilih file baru jika ingin mengganti foto produk spesifik warna tersebut.</p>

                <div class="row g-3 mb-4">
                    <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $varWarna = $produk->varians->firstWhere('warna', $warna);
                        ?>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <label class="form-label fw-bold mb-2">Warna <?php echo e($warna); ?></label>
                                <input type="file" name="gambar_warna[<?php echo e($warna); ?>]" class="form-control mb-2" accept="image/*">
                                <?php if($varWarna && $varWarna->gambar): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e(asset('storage/' . $varWarna->gambar)); ?>" class="rounded border" style="height: 40px; width: 40px; object-fit: cover;">
                                        <span class="text-muted small">Foto warna <?php echo e($warna); ?> aktif</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <hr class="my-4">

                
                <h5 class="fw-bold mb-2 text-primary">Matriks Stok Varian</h5>
                <p class="text-muted small mb-3">Perbarui ketersediaan stok fisik per ukuran dan warna.</p>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">Ukuran / Warna</th>
                                <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($warna); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = \App\Models\Produk::UKURAN_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ukuran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="fw-bold bg-light"><?php echo e($ukuran); ?></td>
                                    <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $varianExisting = $produk->varians
                                                ->where('ukuran', $ukuran)
                                                ->where('warna', $warna)
                                                ->first();
                                            $stokVal = $varianExisting ? $varianExisting->stok : 0;
                                        ?>
                                        <td>
                                            <input type="number" 
                                                   name="stok_varian[<?php echo e($ukuran); ?>][<?php echo e($warna); ?>]" 
                                                   class="form-control text-center" 
                                                   value="<?php echo e(old("stok_varian.{$ukuran}.{$warna}", $stokVal)); ?>" 
                                                   min="0" 
                                                   required>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('produk.index')); ?>" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru\resources\views/transaksi/produk_edit.blade.php ENDPATH**/ ?>