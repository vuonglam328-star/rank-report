<?php $__env->startSection('title', 'Competitors'); ?>
<?php $__env->startSection('page-title', 'Competitor Intelligence'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Competitors</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="GET" action="<?php echo e(route('competitors.index')); ?>">
<div class="card card-outline card-warning shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="mb-1 small">Client</label>
                <select name="client_id" class="form-control form-control-sm" id="clientSel">
                    <option value="">-- Chọn Client --</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php echo e(request('client_id') == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="mb-1 small">Main Project</label>
                <select name="project_id" class="form-control form-control-sm">
                    <option value="">-- Chọn Project --</option>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php echo e(request('project_id') == $p->id ? 'selected' : ''); ?>>
                            <?php echo e($p->name); ?> (<?php echo e($p->domain_clean); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-warning btn-sm btn-block">
                    <i class="fas fa-filter mr-1"></i>Chọn
                </button>
            </div>
        </div>

        <?php if($selectedProject && $availableCompetitors->isNotEmpty()): ?>
        <div class="row mt-2">
            <div class="col-12">
                <label class="mb-1 small font-weight-bold">Chọn đối thủ để so sánh:</label>
                <div class="d-flex flex-wrap">
                    <?php $__currentLoopData = $availableCompetitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" class="custom-control-input" id="c_<?php echo e($comp->id); ?>"
                               name="competitor_ids[]" value="<?php echo e($comp->id); ?>"
                               <?php echo e(in_array($comp->id, $selectedCompetitorIds) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="c_<?php echo e($comp->id); ?>">
                            <?php echo e($comp->domain_clean); ?>

                        </label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <button type="submit" class="btn btn-sm btn-outline-warning ml-2">Cập nhật</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</form>

<?php if(!$selectedProject): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-users fa-4x mb-3 d-block opacity-25"></i>
    <h4>Chọn client và project để xem competitor analysis</h4>
</div>
<?php elseif(empty($selectedCompetitorIds)): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>
    Chọn ít nhất một đối thủ để so sánh.
    <?php if($availableCompetitors->isEmpty()): ?>
        <a href="<?php echo e(route('projects.show', $selectedProject)); ?>" class="alert-link">
            Gán đối thủ cho project này trước.
        </a>
    <?php endif; ?>
</div>
<?php else: ?>


<div class="card card-outline card-warning shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Bảng so sánh — <?php echo e($selectedProject->domain_clean); ?></h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Domain</th>
                    <th class="text-center">Total KWs</th>
                    <th class="text-center">Visibility Score</th>
                    <th class="text-center">Share of Voice</th>
                    <th class="text-center">Overlap</th>
                    <th class="text-center">Wins vs Main</th>
                    <th class="text-center">Snapshot</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $analysis['domain_metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($m['is_main'] ? 'table-primary font-weight-bold' : ''); ?>">
                    <td>
                        <?php if($m['is_main']): ?><i class="fas fa-home mr-1 text-primary"></i><?php endif; ?>
                        <?php echo e($m['domain']); ?>

                        <?php if($m['is_main']): ?><span class="badge badge-primary ml-1">Main</span><?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e(number_format($m['total_keywords'])); ?></td>
                    <td class="text-center"><?php echo e(number_format($m['visibility_score'])); ?></td>
                    <td class="text-center">
                        <div class="progress" style="height:18px;">
                            <div class="progress-bar bg-<?php echo e($m['is_main'] ? 'primary' : 'warning'); ?>"
                                 style="width:<?php echo e(min($m['share_of_voice'], 100)); ?>%">
                                <?php echo e($m['share_of_voice']); ?>%
                            </div>
                        </div>
                    </td>
                    <td class="text-center"><?php echo e(number_format($m['overlap_with_main'])); ?></td>
                    <td class="text-center">
                        <?php if(!$m['is_main']): ?>
                            <span class="<?php echo e($m['wins_vs_main'] > 0 ? 'text-danger' : 'text-muted'); ?>">
                                <?php echo e(number_format($m['wins_vs_main'])); ?>

                            </span>
                        <?php else: ?> —<?php endif; ?>
                    </td>
                    <td class="text-center text-muted small"><?php echo e($m['snapshot_date']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Visibility Score Comparison</h3></div>
            <div class="card-body"><canvas id="visBarChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header"><h3 class="card-title">Share of Voice</h3></div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="sovDoughnut" style="max-height:250px;"></canvas>
            </div>
        </div>
    </div>
</div>


<?php if($sovTimeline && !empty($sovTimeline['labels'])): ?>
<div class="card card-outline card-warning shadow-sm">
    <div class="card-header"><h3 class="card-title">Xu hướng Visibility Score theo thời gian</h3></div>
    <div class="card-body"><canvas id="sovTrendChart" height="80"></canvas></div>
</div>
<?php endif; ?>


<?php if(!empty($analysis['overlap_details'])): ?>
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Keyword Overlap Details</h3>
    </div>
    <div class="card-body">
        <?php $__currentLoopData = $analysis['overlap_details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projectId => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <h5>Main vs <strong><?php echo e($detail['domain']); ?></strong>
            <span class="badge badge-secondary ml-1"><?php echo e(count($detail['keywords'])); ?> keywords overlap</span>
        </h5>
        <div style="max-height:250px; overflow-y:auto; margin-bottom:20px;">
        <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
            <thead class="thead-light">
                <tr>
                    <th>Keyword</th>
                    <th class="text-center">Main</th>
                    <th class="text-center"><?php echo e($detail['domain']); ?></th>
                    <th class="text-center">Winner</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $detail['keywords']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($kw['winner'] === 'competitor' ? 'table-danger' : 'table-success'); ?>">
                    <td><?php echo e($kw['keyword']); ?></td>
                    <td class="text-center"><?php echo e($kw['main_pos'] ?? '—'); ?></td>
                    <td class="text-center"><?php echo e($kw['comp_pos'] ?? '—'); ?></td>
                    <td class="text-center">
                        <span class="badge badge-<?php echo e($kw['winner'] === 'main' ? 'success' : 'danger'); ?>">
                            <?php echo e($kw['winner'] === 'main' ? 'Main ✓' : $detail['domain']); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($analysis && !empty($analysis['charts'])): ?>
<script>
const compCharts = <?php echo json_encode($analysis['charts'], 15, 512) ?>;

new Chart(document.getElementById('visBarChart'), {
    type: 'bar',
    data: compCharts.visibility_bar,
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('sovDoughnut'), {
    type: 'doughnut',
    data: compCharts.sov_doughnut,
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

<?php if($sovTimeline && !empty($sovTimeline['labels'])): ?>
new Chart(document.getElementById('sovTrendChart'), {
    type: 'line',
    data: <?php echo json_encode($sovTimeline, 15, 512) ?>,
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Visibility Score' } } }
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<script>
// ── Auto-submit khi đổi Client: load projects → submit ───────────────────────
document.getElementById('clientSel').addEventListener('change', function () {
    const clientId = this.value;
    const projSel  = document.querySelector('select[name="project_id"]');

    projSel.innerHTML = '<option value="">Đang tải...</option>';
    projSel.disabled  = true;

    if (!clientId) {
        projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
        projSel.disabled  = false;
        return;
    }

    fetch(`/api/projects-by-client/${clientId}`)
        .then(r => r.json())
        .then(data => {
            projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
            data.forEach(p => projSel.add(new Option(`${p.name} (${p.domain})`, p.id)));
            projSel.disabled = false;

            // Tự chọn project đầu tiên và submit
            if (data.length > 0) {
                projSel.selectedIndex = 1;
                projSel.closest('form').submit();
            }
        })
        .catch(() => {
            projSel.innerHTML = '<option value="">-- Chọn Project --</option>';
            projSel.disabled  = false;
        });
});

// ── Auto-submit khi đổi Project ───────────────────────────────────────────────
document.querySelector('select[name="project_id"]').addEventListener('change', function () {
    if (this.value) this.closest('form').submit();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/competitors/index.blade.php ENDPATH**/ ?>