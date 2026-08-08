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

                <?php
                    $warnaAktif = old('warna', $produk->varians->pluck('warna')->unique()->values()->all());
                    $stokTersimpan = $produk->varians->groupBy('warna')->map(fn ($g) => $g->pluck('stok', 'ukuran'));
                    $gambarTersimpan = $produk->varians->groupBy('warna')->map(fn ($g) => $g->first(fn ($v) => $v->gambar)?->gambar);
                ?>

                
                <h5 class="fw-bold mb-2 text-primary">Warna yang Tersedia (maks. <?php echo e(\App\Models\Produk::MAX_WARNA_PER_PRODUK); ?> warna)</h5>
                <p class="text-muted small mb-3">Hilangkan centang untuk menghapus warna itu dari produk (gambar & stoknya ikut terhapus).</p>

                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="form-check">
                            <input class="form-check-input warna-checkbox" type="checkbox" name="warna[]"
                                   value="<?php echo e($warna); ?>" id="warna-<?php echo e($i); ?>"
                                   <?php echo e(in_array($warna, $warnaAktif) ? 'checked' : ''); ?>

                                   onchange="toggleWarnaPanel(this, <?php echo e($i); ?>)">
                            <label class="form-check-label" for="warna-<?php echo e($i); ?>"><?php echo e($warna); ?></label>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div id="warna-panels" class="mb-4">
                    <?php $__currentLoopData = \App\Models\Produk::WARNA_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $warna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $tampil = in_array($warna, $warnaAktif); ?>
                        <div class="warna-panel border rounded-3 p-3 mb-3 bg-light <?php echo e($tampil ? '' : 'd-none'); ?>" id="panel-<?php echo e($i); ?>" data-warna="<?php echo e($warna); ?>">
                            <h6 class="fw-bold mb-3">Warna: <?php echo e($warna); ?></h6>

                            <div class="mb-3">
                                <label class="form-label">Gambar untuk warna <?php echo e($warna); ?></label>

                                <?php if(!empty($gambarTersimpan[$warna])): ?>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <img src="<?php echo e(asset('storage/' . $gambarTersimpan[$warna])); ?>" class="rounded border" style="height: 50px; width: 50px; object-fit: cover;">
                                        <span class="text-muted small">Foto warna <?php echo e($warna); ?> aktif</span>
                                    </div>
                                <?php endif; ?>

                                <input type="file" name="gambar_warna[<?php echo e($warna); ?>]" class="form-control" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar warna ini.</small>
                            </div>

                            <label class="form-label">Stok per Ukuran</label>
                            <div class="row g-2">
                                <?php $__currentLoopData = \App\Models\Produk::UKURAN_OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ukuran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label small text-muted mb-1"><?php echo e($ukuran); ?></label>
                                        <input type="number" name="stok_varian[<?php echo e($warna); ?>][<?php echo e($ukuran); ?>]"
                                               class="form-control" min="0"
                                               value="<?php echo e(old('stok_varian.'.$warna.'.'.$ukuran, $stokTersimpan[$warna][$ukuran] ?? 0)); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('produk.index')); ?>" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const MAX_WARNA = <?php echo e(\App\Models\Produk::MAX_WARNA_PER_PRODUK); ?>;

    function toggleWarnaPanel(checkbox, index) {
        const checked = document.querySelectorAll('.warna-checkbox:checked');

        if (checked.length > MAX_WARNA) {
            checkbox.checked = false;
            alert('Maksimal ' + MAX_WARNA + ' warna per produk ya, biar nggak kewalahan urus gambar & stoknya.');
            return;
        }

        const panel = document.getElementById('panel-' + index);
        panel.classList.toggle('d-none', !checkbox.checked);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru-fixed\resources\views/transaksi/produk_edit.blade.php ENDPATH**/ ?>