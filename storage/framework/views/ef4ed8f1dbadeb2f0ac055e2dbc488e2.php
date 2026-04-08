<?php $__env->startSection('title', 'Clients'); ?>
<?php $__env->startSection('page-title', 'Danh sách Clients'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Clients</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col">
        <a href="<?php echo e(route('clients.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Thêm Client mới
        </a>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-building mr-2"></i>Clients (<?php echo e($clients->total()); ?>)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Client / Company</th>
                    <th>Domain</th>
                    <th>Contact</th>
                    <th class="text-center">Projects</th>
                    <th class="text-center">Tần suất báo cáo</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo e($client->logo_url); ?>" alt="" class="img-circle mr-2" style="width:32px;height:32px;object-fit:cover;">
                            <div>
                                <strong><?php echo e($client->name); ?></strong>
                                <?php if($client->company_name && $client->company_name !== $client->name): ?>
                                    <br><small class="text-muted"><?php echo e($client->company_name); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if($client->domain): ?>
                            <a href="https://<?php echo e($client->domain_clean); ?>" target="_blank" rel="noopener">
                                <?php echo e($client->domain_clean); ?>

                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo e($client->contact_name ?? '—'); ?>

                        <?php if($client->contact_email): ?>
                            <br><small class="text-muted"><?php echo e($client->contact_email); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info"><?php echo e($client->projects_count); ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary"><?php echo e($client->report_frequency); ?></span>
                    </td>
                    <td class="text-right">
                        <a href="<?php echo e(route('clients.show', $client)); ?>" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?php echo e(route('clients.destroy', $client)); ?>" class="d-inline"
                              onsubmit="return confirm('Xóa client <?php echo e($client->name); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-building fa-2x mb-2 d-block"></i>
                        Chưa có client nào. <a href="<?php echo e(route('clients.create')); ?>">Thêm ngay</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($clients->hasPages()): ?>
    <div class="card-footer">
        <?php echo e($clients->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/clients/index.blade.php ENDPATH**/ ?>