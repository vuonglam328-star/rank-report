<?php $__env->startSection('title', $client->name); ?>
<?php $__env->startSection('page-title', $client->name); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('clients.index')); ?>">Clients</a></li>
    <li class="breadcrumb-item active"><?php echo e($client->name); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Thông tin Client</h3>
                <div class="card-tools">
                    <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-xs btn-warning">
                        <i class="fas fa-edit mr-1"></i>Sửa
                    </a>
                </div>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo e($client->logo_url); ?>" alt="<?php echo e($client->name); ?>"
                     class="img-circle elevation-2 mb-3" style="width:80px;height:80px;object-fit:cover;">
                <h4 class="mb-0"><?php echo e($client->name); ?></h4>
                <?php if($client->company_name && $client->company_name !== $client->name): ?>
                    <p class="text-muted"><?php echo e($client->company_name); ?></p>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <dl class="row mb-0">
                    <?php if($client->domain): ?>
                    <dt class="col-5">Domain</dt>
                    <dd class="col-7">
                        <a href="https://<?php echo e($client->domain_clean); ?>" target="_blank" rel="noopener">
                            <?php echo e($client->domain_clean); ?>

                        </a>
                    </dd>
                    <?php endif; ?>
                    <?php if($client->contact_name): ?>
                    <dt class="col-5">Liên hệ</dt>
                    <dd class="col-7"><?php echo e($client->contact_name); ?></dd>
                    <?php endif; ?>
                    <?php if($client->contact_email): ?>
                    <dt class="col-5">Email</dt>
                    <dd class="col-7 text-truncate">
                        <a href="mailto:<?php echo e($client->contact_email); ?>"><?php echo e($client->contact_email); ?></a>
                    </dd>
                    <?php endif; ?>
                    <dt class="col-5">Báo cáo</dt>
                    <dd class="col-7"><span class="badge badge-secondary"><?php echo e($client->report_frequency); ?></span></dd>
                    <dt class="col-5">Projects</dt>
                    <dd class="col-7"><span class="badge badge-info"><?php echo e($client->projects_count); ?></span></dd>
                    <dt class="col-5">Ngày tạo</dt>
                    <dd class="col-7 text-muted small"><?php echo e($client->created_at->format('d/m/Y')); ?></dd>
                </dl>
                <?php if($client->notes): ?>
                    <hr>
                    <small class="text-muted"><i class="fas fa-sticky-note mr-1"></i><?php echo e($client->notes); ?></small>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?php echo e(route('projects.create', ['client_id' => $client->id])); ?>" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-plus mr-1"></i>Thêm Project mới
                </a>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-globe mr-2"></i>Projects (<?php echo e($projects->count()); ?>)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Project / Domain</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Snapshots</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($project->name); ?></strong>
                                <br><small class="text-muted"><?php echo e($project->domain_clean); ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?php echo e($project->project_type_badge); ?>">
                                    <?php echo e(strtoupper($project->project_type)); ?>

                                </span>
                            </td>
                            <td class="text-center"><?php echo e($project->snapshots_count); ?></td>
                            <td class="text-center">
                                <span class="badge badge-<?php echo e($project->status === 'active' ? 'success' : 'secondary'); ?>">
                                    <?php echo e($project->status); ?>

                                </span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('projects.show', $project)); ?>" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('dashboard', ['project_id' => $project->id])); ?>" class="btn btn-xs btn-primary">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="<?php echo e(route('imports.create', ['project_id' => $project->id])); ?>" class="btn btn-xs btn-success">
                                    <i class="fas fa-upload"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Chưa có project nào.
                                <a href="<?php echo e(route('projects.create', ['client_id' => $client->id])); ?>">Thêm ngay</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-4">
                <a href="<?php echo e(route('dashboard', ['client_id' => $client->id])); ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo e(route('reports.create')); ?>" class="btn btn-success btn-block">
                    <i class="fas fa-file-pdf mr-1"></i>Tạo báo cáo
                </a>
            </div>
            <div class="col-md-4">
                <form method="POST" action="<?php echo e(route('clients.destroy', $client)); ?>"
                      onsubmit="return confirm('Xóa client <?php echo e(addslashes($client->name)); ?> và toàn bộ dữ liệu?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-outline-danger btn-block">
                        <i class="fas fa-trash mr-1"></i>Xóa Client
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/clients/show.blade.php ENDPATH**/ ?>