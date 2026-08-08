
<div class="dropdown">
    <button class="btn btn-light border position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill text-secondary"></i>
        <?php if(($notifikasiBelumDibacaCount ?? 0) > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo e($notifikasiBelumDibacaCount); ?>

                <span class="visually-hidden">notifikasi belum dibaca</span>
            </span>
        <?php endif; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width: 340px;">
        <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bell-fill text-secondary me-1"></i> Notifikasi</strong>
            <?php if(($notifikasiBelumDibacaCount ?? 0) > 0): ?>
                <form action="<?php echo e(route('notifikasi.bacaSemua')); ?>" method="POST" class="m-0">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Tandai semua dibaca</button>
                </form>
            <?php endif; ?>
        </div>

        <ul class="list-unstyled mb-0" style="max-height: 340px; overflow-y: auto;">
            <?php $__empty_1 = true; $__currentLoopData = ($notifikasiList ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $tujuan = null;
                    if ($notif->transaksi_id) {
                        $tujuan = auth()->user()->role === 'admin'
                            ? route('transaksi.show', $notif->transaksi_id)
                            : route('customer.pesanan.show', $notif->transaksi_id);
                    }
                ?>
                <li class="px-3 py-2 border-bottom <?php echo e($notif->dibaca_at ? '' : 'bg-light'); ?>">
                    <?php if($tujuan): ?>
                        <a href="<?php echo e($tujuan); ?>" class="d-flex gap-2 text-decoration-none text-dark">
                    <?php else: ?>
                        <div class="d-flex gap-2">
                    <?php endif; ?>
                        <i class="bi <?php echo e($notif->iconClass()); ?> fs-5 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?php echo e($notif->judul); ?></div>
                            <div class="small text-muted"><?php echo e($notif->pesan); ?></div>
                            <div class="small text-muted mt-1"><?php echo e($notif->created_at->diffForHumans()); ?></div>
                        </div>
                    <?php if($tujuan): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="px-3 py-4 text-center text-muted small">Belum ada notifikasi.</li>
            <?php endif; ?>
        </ul>
    </div>
</div><?php /**PATH C:\Users\Cut Aidila Safriana\Downloads\fashion-sales-terbaru\resources\views/partials/_notifikasi_bell.blade.php ENDPATH**/ ?>