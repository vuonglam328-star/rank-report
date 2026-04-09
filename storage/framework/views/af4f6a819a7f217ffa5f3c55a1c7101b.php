<?php $__env->startSection('title', $project->name); ?>
<?php $__env->startSection('page-title', $project->name); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('projects.index')); ?>">Projects</a></li>
    <li class="breadcrumb-item active"><?php echo e($project->name); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header"><h3 class="card-title">Project Info</h3>
                <div class="card-tools">
                    <a href="<?php echo e(route('projects.edit', $project)); ?>" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Client</dt>
                    <dd class="col-7"><?php echo e($project->client->name); ?></dd>
                    <dt class="col-5">Domain</dt>
                    <dd class="col-7"><a href="https://<?php echo e($project->domain_clean); ?>" target="_blank"><?php echo e($project->domain_clean); ?></a></dd>
                    <dt class="col-5">Type</dt>
                    <dd class="col-7"><span class="badge badge-<?php echo e($project->project_type_badge); ?>"><?php echo e($project->project_type); ?></span></dd>
                    <dt class="col-5">Country</dt>
                    <dd class="col-7"><?php echo e($project->country_code); ?></dd>
                    <dt class="col-5">Device</dt>
                    <dd class="col-7"><?php echo e($project->device_type); ?></dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7"><span class="badge badge-<?php echo e($project->status === 'active' ? 'success' : 'secondary'); ?>"><?php echo e($project->status); ?></span></dd>
                    <dt class="col-5">Snapshots</dt>
                    <dd class="col-7"><span class="badge badge-info"><?php echo e($project->snapshots_count); ?></span></dd>
                    <dt class="col-5">Keywords</dt>
                    <dd class="col-7"><span class="badge badge-secondary"><?php echo e($project->keywords_count); ?></span></dd>
                </dl>
                <?php if($project->notes): ?>
                    <hr><small class="text-muted"><?php echo e($project->notes); ?></small>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="<?php echo e(route('imports.create', ['project_id' => $project->id])); ?>" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-upload mr-1"></i>Import CSV mới
                </a>
            </div>
        </div>

        
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Competitors (<?php echo e($competitors->count()); ?>)</h3></div>
            <div class="card-body">
                <?php if($competitors->isEmpty()): ?>
                    <p class="text-muted">Chưa có đối thủ được gán.</p>
                <?php endif; ?>
                <?php $__currentLoopData = $competitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge badge-light border mr-2"><?php echo e($comp->project_type); ?></span>
                        <a href="<?php echo e(route('projects.show', $comp)); ?>"><?php echo e($comp->domain_clean); ?></a>
                        <small class="text-muted ml-1">(<?php echo e($comp->name); ?>)</small>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-outline-warning btn-block" data-toggle="modal" data-target="#competitorModal">
                    <i class="fas fa-users-cog mr-1"></i>Quản lý Competitors
                </button>
            </div>
        </div>
    </div>

    
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Snapshot History</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ngày</th>
                            <th>Tên Snapshot</th>
                            <th class="text-center">Keywords</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $snapshots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $snap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($snap->report_date->format('d/m/Y')); ?></strong></td>
                            <td><?php echo e($snap->snapshot_name); ?></td>
                            <td class="text-center"><?php echo e(number_format($snap->total_keywords)); ?></td>
                            <td class="text-center">
                                <span class="badge badge-<?php echo e($snap->status_badge); ?>"><?php echo e($snap->status); ?></span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('dashboard', ['project_id' => $project->id, 'snapshot_id' => $snap->id])); ?>"
                                   class="btn btn-xs btn-info">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="<?php echo e(route('imports.show', $snap)); ?>" class="btn btn-xs btn-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('imports.destroy', $snap)); ?>" class="d-inline"
                                      onsubmit="return confirm('Xóa snapshot này?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có snapshot nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($snapshots->count() === 10): ?>
            <div class="card-footer">
                <a href="<?php echo e(route('imports.index', ['project_id' => $project->id])); ?>" class="btn btn-sm btn-outline-secondary">
                    Xem tất cả snapshots
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="text-center">
            <a href="<?php echo e(route('dashboard', ['project_id' => $project->id])); ?>" class="btn btn-primary">
                <i class="fas fa-tachometer-alt mr-1"></i>Xem Dashboard
            </a>
            <a href="<?php echo e(route('keywords.index', ['project_id' => $project->id])); ?>" class="btn btn-info ml-2">
                <i class="fas fa-search mr-1"></i>Xem Keywords
            </a>
            <a href="<?php echo e(route('competitors.index', ['project_id' => $project->id, 'client_id' => $project->client_id])); ?>" class="btn btn-warning ml-2">
                <i class="fas fa-users mr-1"></i>Competitor Analysis
            </a>
        </div>
    </div>
</div>


<div class="modal fade" id="competitorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quản lý Competitors — <?php echo e($project->name); ?></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="<?php echo e(route('projects.sync-competitors', $project)); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p class="text-muted">Chọn các projects là đối thủ cạnh tranh:</p>
                    <?php
                        $allProjects = \App\Models\Project::where('id', '!=', $project->id)
                            ->where('status', 'active')
                            ->with('client')
                            ->orderBy('client_id')
                            ->orderBy('name')
                            ->get();
                        $existingIds = $competitors->pluck('id')->toArray();
                    ?>
                    <?php $__currentLoopData = $allProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="cc_<?php echo e($p->id); ?>"
                               name="competitor_ids[]" value="<?php echo e($p->id); ?>"
                               <?php echo e(in_array($p->id, $existingIds) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="cc_<?php echo e($p->id); ?>">
                            <strong><?php echo e($p->client->name); ?></strong> — <?php echo e($p->name); ?> (<?php echo e($p->domain_clean); ?>)
                        </label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">Lưu Competitors</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/projects/show.blade.php ENDPATH**/ ?>