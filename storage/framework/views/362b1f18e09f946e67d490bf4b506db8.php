<?php $__env->startSection('title', 'Projects'); ?>
<?php $__env->startSection('page-title', 'Danh sách Projects'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Projects</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="<?php echo e(route('projects.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Thêm Project mới
        </a>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex align-items-center">
            <select name="client_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả clients</option>
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php echo e(request('client_id') == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="project_type" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <option value="main"       <?php echo e(request('project_type') === 'main'       ? 'selected' : ''); ?>>Main</option>
                <option value="competitor" <?php echo e(request('project_type') === 'competitor' ? 'selected' : ''); ?>>Competitor</option>
            </select>
            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tất cả status</option>
                <option value="active"   <?php echo e(request('status') === 'active'   ? 'selected' : ''); ?>>Active</option>
                <option value="paused"   <?php echo e(request('status') === 'paused'   ? 'selected' : ''); ?>>Paused</option>
                <option value="archived" <?php echo e(request('status') === 'archived' ? 'selected' : ''); ?>>Archived</option>
            </select>
        </form>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-globe mr-2"></i>Projects (<?php echo e($projects->total()); ?>)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Project / Domain</th>
                    <th>Client</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Snapshots</th>
                    <th class="text-center">Keywords</th>
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
                    <td><small><?php echo e($project->client->name); ?></small></td>
                    <td class="text-center">
                        <span class="badge badge-<?php echo e($project->project_type_badge); ?>"><?php echo e(strtoupper($project->project_type)); ?></span>
                    </td>
                    <td class="text-center"><?php echo e(number_format($project->snapshots_count)); ?></td>
                    <td class="text-center"><?php echo e(number_format($project->keywords_count)); ?></td>
                    <td class="text-center">
                        <span class="badge badge-<?php echo e($project->status === 'active' ? 'success' : ($project->status === 'paused' ? 'warning' : 'secondary')); ?>">
                            <?php echo e($project->status); ?>

                        </span>
                    </td>
                    <td class="text-right">
                        <a href="<?php echo e(route('projects.show', $project)); ?>" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <a href="<?php echo e(route('imports.create', ['project_id' => $project->id])); ?>" class="btn btn-xs btn-success" title="Import CSV"><i class="fas fa-upload"></i></a>
                        <a href="<?php echo e(route('projects.edit', $project)); ?>" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="<?php echo e(route('projects.destroy', $project)); ?>" class="d-inline"
                              onsubmit="return confirm('Xóa project <?php echo e($project->name); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-globe fa-2x mb-2 d-block opacity-25"></i>
                        Chưa có project nào. <a href="<?php echo e(route('projects.create')); ?>">Thêm ngay</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($projects->hasPages()): ?>
    <div class="card-footer"><?php echo e($projects->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/projects/index.blade.php ENDPATH**/ ?>