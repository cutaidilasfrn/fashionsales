

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Data Platform</h2>
            <p class="text-muted mb-0">Daftar platform penjualan beserta jumlah transaksi</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('platform.index')); ?>" class="row mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari platform..." value="<?php echo e(request('q')); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
                <div class="col-md-5 text-end">
                    <a href="<?php echo e(route('platform.create')); ?>" class="btn btn-success"><i class="bi bi-plus-circle"></i> Tambah Platform</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Platform</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th width="170" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration + ($platforms->firstItem() - 1)); ?></td>
                            <td><?php echo e($platform->nama_platform); ?></td>
                            <td class="text-center"><span class="badge bg-primary"><?php echo e($platform->total_transaksi); ?></span></td>
                            <td class="text-center">
                                <a href="<?php echo e(route('platform.edit',$platform->id)); ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?php echo e(route('platform.destroy',$platform->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus platform ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data platform.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <?php echo e($platforms->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\fashion-sales\resources\views/transaksi/platform.blade.php ENDPATH**/ ?>