

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Daftar Produk</h4>
        <a href="<?php echo e(route('produk.create')); ?>" class="btn btn-primary">+ Tambah Produk</a>
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 90px;">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Material</th>
                            <th>Harga Satuan</th>
                            <th>Diskon</th>
                            <th>Stok</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td>
                                    <?php if($p->gambar): ?>
                                        <img src="<?php echo e(asset('storage/' . $p->gambar)); ?>" alt="<?php echo e($p->nama_produk); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded border">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold"><?php echo e($p->nama_produk); ?></td>
                                <td><?php echo e($p->material ?? '-'); ?></td>
                                <td>Rp <?php echo e(number_format($p->harga_satuan, 0, ',', '.')); ?></td>
                                <td>
                                    <?php if($p->diskon_persen > 0): ?>
                                        <span class="badge bg-danger"><?php echo e($p->diskon_persen); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($p->stok <= \App\Models\Produk::BATAS_STOK_MENIPIS): ?>
                                        <span class="badge bg-warning text-dark" title="Stok menipis"><?php echo e($p->stok); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border"><?php echo e($p->stok); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success me-1" data-bs-toggle="modal" data-bs-target="#stokModal<?php echo e($p->id); ?>">
                                        + Stok
                                    </button>
                                    <a href="<?php echo e(route('produk.edit', $p->id)); ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                                    <form action="<?php echo e(route('produk.destroy', $p->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Tambah Stok -->
                            <div class="modal fade" id="stokModal<?php echo e($p->id); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?php echo e(route('produk.tambah-stok', $p->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tambah Stok: <?php echo e($p->nama_produk); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted mb-2">Stok saat ini: <strong><?php echo e($p->stok); ?></strong></p>
                                                <label class="form-label">Jumlah yang ditambahkan</label>
                                                <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Tambah Stok</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/transaksi/produk.blade.php ENDPATH**/ ?>