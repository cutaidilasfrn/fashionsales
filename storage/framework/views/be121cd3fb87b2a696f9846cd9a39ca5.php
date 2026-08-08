

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Data Pelanggan</h2>
            <p class="text-muted mb-0">Daftar seluruh pelanggan beserta jumlah transaksi</p>
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
            <form method="GET" action="<?php echo e(route('pelanggan.index')); ?>" class="row mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama pelanggan, kota, atau gender..." value="<?php echo e(request('q')); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
                <div class="col-md-7"></div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Jenis Kelamin</th>
                            <th>Kota</th>
                            <th class="text-center">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pelanggans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pelanggan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration + ($pelanggans->firstItem() - 1)); ?></td>
                            <td><?php echo e($pelanggan->nama_pelanggan); ?></td>
                            <td><?php echo e($pelanggan->jenis_kelamin); ?></td>
                            <td><?php echo e($pelanggan->kota); ?></td>
                            <td class="text-center"><span class="badge bg-success"><?php echo e($pelanggan->total_transaksi); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data pelanggan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <?php echo e($pelanggans->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru\resources\views/transaksi/pelanggan.blade.php ENDPATH**/ ?>