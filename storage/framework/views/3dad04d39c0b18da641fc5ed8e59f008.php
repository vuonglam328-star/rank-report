<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'SEO Dashboard'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Position change colors ─────────────────────────────── */
.change-up   { color: #28a745; font-weight: 600; }
.change-down { color: #dc3545; font-weight: 600; }
.change-none { color: #6c757d; }

/* ── KPI small-box tweaks ────────────────────────────────── */
.small-box .kpi-delta {
    font-size: .72rem;
    opacity: .9;
    display: block;
    margin-top: 2px;
}
.small-box .inner p { font-size: .95rem; margin-bottom: 2px; }
.small-box .inner h3 { font-size: 2rem; margin-bottom: 0; }

/* ── Visibility score card ───────────────────────────────── */
.visibility-card .inner h3 { font-size: 2.6rem; }

/* ── Sortable table headers ──────────────────────────────── */
th.sortable {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
th.sortable:hover { background: rgba(0,0,0,.04); }
th.sortable .sort-icon {
    display:inline-block; width:1em; text-align:center;
    font-style:normal; font-size:.75em; margin-left:2px;
    color:#adb5bd;
}
th.sortable .sort-icon::after        { content:'⇅'; }
th.sortable.asc  .sort-icon::after   { content:'▲'; color:#007bff; }
th.sortable.desc .sort-icon::after   { content:'▼'; color:#007bff; }


#allUrlsModal .modal-body { max-height: 65vh; overflow-y: auto; }
#allUrlsModal thead th,
#urlKeywordsModal thead th {
    position: sticky; top: 0; background: #fff; z-index: 1;
}

/* ── Competitor SoV chart area ───────────────────────────── */
.comp-chart-wrap { position: relative; height: 260px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<form method="GET" action="<?php echo e(route('dashboard')); ?>" id="filterForm">
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body py-2">

        
        <div class="row align-items-end">

            
            <div class="col-md-3 col-sm-6 mb-2">
                <label class="mb-1 small font-weight-bold">Client</label>
                <select name="client_id" id="clientSelect" class="form-control form-control-sm">
                    <option value="">-- Chọn Client --</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"
                            <?php echo e($selectedClient && $selectedClient->id == $c->id ? 'selected' : ''); ?>>
                            <?php echo e($c->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="col-md-3 col-sm-6 mb-2">
                <label class="mb-1 small font-weight-bold">Project</label>
                <select name="project_id" id="projectSelect" class="form-control form-control-sm"
                    <?php echo e(!$selectedClient ? 'disabled' : ''); ?>>
                    <option value="">-- Chọn Project --</option>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"
                            <?php echo e($selectedProject && $selectedProject->id == $p->id ? 'selected' : ''); ?>>
                            <?php echo e($p->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="mb-1 small font-weight-bold">Snapshot</label>
                <select name="snapshot_id" id="snapshotSelect" class="form-control form-control-sm"
                    <?php echo e(!$selectedProject ? 'disabled' : ''); ?>>
                    <option value="">-- Mới nhất --</option>
                    <?php $__currentLoopData = $snapshots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"
                            <?php echo e($selectedSnapshot && $selectedSnapshot->id == $s->id ? 'selected' : ''); ?>>
                            <?php echo e($s->report_date->format('d/m/Y')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="col-md-2 col-sm-6 mb-2">
                <label class="mb-1 small font-weight-bold">Khoảng thời gian</label>
                <select name="date_range" id="dateRangeSelect" class="form-control form-control-sm">
                    <option value="7d"  <?php echo e($dateRange === '7d'  ? 'selected' : ''); ?>>7 ngày</option>
                    <option value="30d" <?php echo e($dateRange === '30d' ? 'selected' : ''); ?>>30 ngày</option>
                    <option value="90d" <?php echo e($dateRange === '90d' ? 'selected' : ''); ?>>90 ngày</option>
                    <option value="6m"  <?php echo e($dateRange === '6m'  ? 'selected' : ''); ?>>6 tháng</option>
                    <option value="12m" <?php echo e($dateRange === '12m' ? 'selected' : ''); ?>>12 tháng</option>
                    <option value="all" <?php echo e($dateRange === 'all' ? 'selected' : ''); ?>>Tất cả</option>
                </select>
            </div>

            
            <div class="col-md-2 col-sm-6 mb-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-sync-alt mr-1"></i>Cập nhật
                </button>
            </div>

        </div>

        
        <?php if($selectedProject && $availableCompetitors->count()): ?>
        <div class="row align-items-center border-top pt-2 mt-1">
            <div class="col-auto">
                <small class="font-weight-bold text-muted">
                    <i class="fas fa-flag-checkered mr-1"></i>So sánh đối thủ:
                </small>
            </div>
            <?php $__currentLoopData = $availableCompetitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-auto">
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="comp_<?php echo e($comp->id); ?>"
                           name="competitors[]"
                           value="<?php echo e($comp->id); ?>"
                           <?php echo e(in_array($comp->id, $selectedCompetitorIds) ? 'checked' : ''); ?>>
                    <label class="custom-control-label small" for="comp_<?php echo e($comp->id); ?>">
                        <?php echo e($comp->name); ?>

                        <?php if($comp->domain): ?>
                            <span class="text-muted">(<?php echo e($comp->domain); ?>)</span>
                        <?php endif; ?>
                    </label>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="col-auto ml-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-chart-bar mr-1"></i>Cập nhật đối thủ
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
</form>


<?php if(!$selectedSnapshot): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-tachometer-alt fa-4x mb-3 d-block" style="opacity:.2;"></i>
    <h5>Chọn Client &amp; Project để xem Dashboard</h5>
    <p class="small">Vui lòng chọn một client và project ở bộ lọc phía trên.</p>
</div>
<?php else: ?>

<?php if($kpis): ?>




<div class="row">

    
    <?php
        $kwDelta = $prevKpis ? ($kpis['total_keywords'] - $prevKpis['total_keywords']) : null;
    ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['total_keywords'])); ?></h3>
                <p>Total Keywords</p>
                <?php if($kwDelta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($kwDelta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($kwDelta); ?>

                        <?php elseif($kwDelta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($kwDelta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-key"></i></div>
            <a href="#" class="small-box-footer">Tổng từ khóa</a>
        </div>
    </div>

    
    <?php
        $avgDelta = $prevKpis ? round($kpis['avg_position'] - $prevKpis['avg_position'], 1) : null;
    ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo e($kpis['avg_position']); ?></h3>
                <p>Avg Position</p>
                <?php if($avgDelta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($avgDelta < 0): ?> <span style="color:#fff;"><i class="fas fa-arrow-up"></i> <?php echo e(abs($avgDelta)); ?></span>
                        <?php elseif($avgDelta > 0): ?> <i class="fas fa-arrow-down"></i> +<?php echo e($avgDelta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-crosshairs"></i></div>
            <a href="#" class="small-box-footer">Vị trí trung bình</a>
        </div>
    </div>

    
    <?php $t3Delta = $prevKpis ? ($kpis['top_3'] - $prevKpis['top_3']) : null; ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['top_3'])); ?></h3>
                <p>Top 3</p>
                <?php if($t3Delta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($t3Delta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($t3Delta); ?>

                        <?php elseif($t3Delta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($t3Delta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-trophy"></i></div>
            <a href="#" class="small-box-footer">Từ khóa vị trí 1–3</a>
        </div>
    </div>

    
    <?php $t10Delta = $prevKpis ? ($kpis['top_10'] - $prevKpis['top_10']) : null; ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['top_10'])); ?></h3>
                <p>Top 10</p>
                <?php if($t10Delta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($t10Delta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($t10Delta); ?>

                        <?php elseif($t10Delta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($t10Delta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-star"></i></div>
            <a href="#" class="small-box-footer">Từ khóa vị trí 1–10</a>
        </div>
    </div>

    
    <?php $t20Delta = $prevKpis ? ($kpis['top_20'] - $prevKpis['top_20']) : null; ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box" style="background:#6f42c1;color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['top_20'])); ?></h3>
                <p>Top 20</p>
                <?php if($t20Delta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($t20Delta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($t20Delta); ?>

                        <?php elseif($t20Delta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($t20Delta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-medal"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">Từ khóa vị trí 1–20</a>
        </div>
    </div>

    
    <?php $t50Delta = $prevKpis ? ($kpis['top_50'] - $prevKpis['top_50']) : null; ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box" style="background:#17a2b8;color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['top_50'])); ?></h3>
                <p>Top 50</p>
                <?php if($t50Delta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($t50Delta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($t50Delta); ?>

                        <?php elseif($t50Delta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($t50Delta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-list-ol"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">Từ khóa vị trí 1–50</a>
        </div>
    </div>

</div>


<div class="row">

    
    <?php $t100Delta = $prevKpis ? ($kpis['top_100'] - $prevKpis['top_100']) : null; ?>
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['top_100'])); ?></h3>
                <p>Top 100</p>
                <?php if($t100Delta !== null): ?>
                    <span class="kpi-delta">
                        <?php if($t100Delta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($t100Delta); ?>

                        <?php elseif($t100Delta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($t100Delta); ?>

                        <?php else: ?> <i class="fas fa-minus"></i> 0 <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-hashtag"></i></div>
            <a href="#" class="small-box-footer">Top 100</a>
        </div>
    </div>

    
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['outside_100'])); ?></h3>
                <p>Ngoài Top 100</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="#" class="small-box-footer">Không vào top 100</a>
        </div>
    </div>

    
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box" style="background:#28a745;color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['improved'])); ?></h3>
                <p>Tăng hạng</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-up"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">Từ khóa cải thiện</a>
        </div>
    </div>

    
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['declined'])); ?></h3>
                <p>Giảm hạng</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
            <a href="#" class="small-box-footer">Từ khóa tụt hạng</a>
        </div>
    </div>

    
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box" style="background:#20c997;color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['new_keywords'])); ?></h3>
                <p>Keyword mới</p>
            </div>
            <div class="icon"><i class="fas fa-plus-circle"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">Mới xuất hiện</a>
        </div>
    </div>

    
    <div class="col-6 col-sm-4 col-lg-2">
        <div class="small-box" style="background:#e83e8c;color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['lost_keywords'])); ?></h3>
                <p>Keyword mất</p>
            </div>
            <div class="icon"><i class="fas fa-minus-circle"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">Biến mất</a>
        </div>
    </div>

</div>


<?php if(isset($kpis['visibility_score'])): ?>
<div class="row mb-1">
    <div class="col-md-4 col-sm-6">
        <div class="small-box visibility-card" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;">
            <div class="inner">
                <h3><?php echo e(number_format($kpis['visibility_score'], 2)); ?><sup style="font-size:1rem;">%</sup></h3>
                <p>Visibility Score</p>
                <?php if($prevKpis && isset($prevKpis['visibility_score'])): ?>
                    <?php $visDelta = round($kpis['visibility_score'] - $prevKpis['visibility_score'], 2); ?>
                    <span class="kpi-delta">
                        vs kỳ trước:
                        <?php if($visDelta > 0): ?> <i class="fas fa-arrow-up"></i> +<?php echo e($visDelta); ?>%
                        <?php elseif($visDelta < 0): ?> <i class="fas fa-arrow-down"></i> <?php echo e($visDelta); ?>%
                        <?php else: ?> <i class="fas fa-minus"></i> Không đổi <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="icon"><i class="fas fa-eye"></i></div>
            <a href="#" class="small-box-footer" style="color:rgba(255,255,255,.7);">
                Chỉ số hiển thị tổng thể
            </a>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="row">

    
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2 text-primary"></i>Avg Position Timeline
                </h3>
                <div class="card-tools">
                    <small class="text-muted"><?php echo e(strtoupper($dateRange)); ?></small>
                </div>
            </div>
            <div class="card-body">
                <?php if($timelineData && !empty($timelineData['labels'])): ?>
                    <canvas id="timelineChart" height="200"></canvas>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-line fa-3x mb-2 d-block" style="opacity:.2;"></i>
                        <p>Chưa đủ dữ liệu để hiển thị timeline.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2 text-success"></i>Position Distribution
                </h3>
            </div>
            <div class="card-body">
                <?php if(isset($kpis['position_distribution']) && !empty($kpis['position_distribution'])): ?>
                    <canvas id="distChart" height="200"></canvas>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-bar fa-3x mb-2 d-block" style="opacity:.2;"></i>
                        <p>Không có dữ liệu phân phối.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>


<div class="row">

    
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-arrow-up mr-2 text-success"></i>Top Winners
                    <span class="badge badge-success ml-1"><?php echo e(count($winners)); ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if(count($winners)): ?>
                <div style="overflow-x:auto;">
                <table class="table table-sm table-hover mb-0 tbl-sort" style="font-size:.85rem;">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-type="text">Keyword <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:80px;">Trước <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:80px;">Sau <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:90px;">Thay đổi <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $winners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-truncate" style="max-width:200px;" title="<?php echo e($w['keyword'] ?? ''); ?>">
                                <?php echo e($w['keyword'] ?? '—'); ?>

                            </td>
                            <td class="text-center text-muted" data-val="<?php echo e($w['previous_position'] ?? 9999); ?>"><?php echo e($w['previous_position'] ?? '—'); ?></td>
                            <td class="text-center" data-val="<?php echo e($w['current_position'] ?? 9999); ?>">
                                <?php
                                    $pos = $w['current_position'] ?? null;
                                    $badgeColor = $pos <= 3 ? 'success' : ($pos <= 10 ? 'info' : ($pos <= 20 ? 'primary' : ($pos <= 50 ? 'warning' : 'secondary')));
                                ?>
                                <?php if($pos): ?>
                                    <span class="badge badge-<?php echo e($badgeColor); ?>"><?php echo e($pos); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-val="<?php echo e($w['position_change'] ?? 0); ?>">
                                <?php if(isset($w['position_change']) && $w['position_change'] > 0): ?>
                                    <span class="change-up">▲ +<?php echo e($w['position_change']); ?></span>
                                <?php elseif(isset($w['position_change']) && $w['position_change'] < 0): ?>
                                    <span class="change-down">▼ <?php echo e($w['position_change']); ?></span>
                                <?php else: ?>
                                    <span class="change-none">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-arrow-up fa-2x mb-2 d-block" style="opacity:.2;"></i>
                    Không có dữ liệu
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 mb-4">
        <div class="card card-outline card-danger shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-arrow-down mr-2 text-danger"></i>Top Losers
                    <span class="badge badge-danger ml-1"><?php echo e(count($losers)); ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if(count($losers)): ?>
                <div style="overflow-x:auto;">
                <table class="table table-sm table-hover mb-0 tbl-sort" style="font-size:.85rem;">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-type="text">Keyword <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:80px;">Trước <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:80px;">Sau <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num" style="width:90px;">Thay đổi <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $losers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-truncate" style="max-width:200px;" title="<?php echo e($l['keyword'] ?? ''); ?>">
                                <?php echo e($l['keyword'] ?? '—'); ?>

                            </td>
                            <td class="text-center text-muted" data-val="<?php echo e($l['previous_position'] ?? 9999); ?>"><?php echo e($l['previous_position'] ?? '—'); ?></td>
                            <td class="text-center" data-val="<?php echo e($l['current_position'] ?? 9999); ?>">
                                <?php
                                    $lpos = $l['current_position'] ?? null;
                                    $lBadge = $lpos <= 3 ? 'success' : ($lpos <= 10 ? 'info' : ($lpos <= 20 ? 'primary' : ($lpos <= 50 ? 'warning' : 'secondary')));
                                ?>
                                <?php if($lpos): ?>
                                    <span class="badge badge-<?php echo e($lBadge); ?>"><?php echo e($lpos); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-val="<?php echo e($l['position_change'] ?? 0); ?>">
                                <?php if(isset($l['position_change']) && $l['position_change'] > 0): ?>
                                    <span class="change-up">▲ +<?php echo e($l['position_change']); ?></span>
                                <?php elseif(isset($l['position_change']) && $l['position_change'] < 0): ?>
                                    <span class="change-down">▼ <?php echo e($l['position_change']); ?></span>
                                <?php else: ?>
                                    <span class="change-none">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-arrow-down fa-2x mb-2 d-block" style="opacity:.2;"></i>
                    Không có dữ liệu
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>


<?php if(!empty($topKeywords)): ?>
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-key mr-2"></i>Top 10 Keywords</h3>
        <div class="card-tools ml-auto">
            <small class="text-muted">Click tiêu đề cột để sắp xếp</small>
        </div>
    </div>
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="table table-sm table-hover mb-0 tbl-sort" style="font-size:.85rem;">
            <thead class="thead-light">
                <tr>
                    <th class="sortable" data-type="text" style="min-width:200px;">Keyword <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Position <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Thay đổi <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Volume <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">Organic Traffic <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num">KD <span class="sort-icon"></span></th>
                    <th class="sortable" data-type="text" style="min-width:160px;">URL <span class="sort-icon"></span></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $topKeywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $pos     = $kw->current_position ?? null;
                    $badge   = $pos <= 3 ? 'success' : ($pos <= 10 ? 'info' : ($pos <= 20 ? 'primary' : ($pos <= 50 ? 'warning' : 'secondary')));
                    $chg     = $kw->position_change ?? 0;
                    $urlPath = $kw->target_url ? preg_replace('#^https?://[^/]+#', '', $kw->target_url) ?: '/' : null;
                    $kdVal   = ($kw->kd !== null && $kw->kd !== '') ? (int)$kw->kd : null;
                ?>
                <tr>
                    <td class="text-truncate" style="max-width:220px;" title="<?php echo e($kw->keyword); ?>"><?php echo e($kw->keyword); ?></td>
                    <td class="text-center" data-val="<?php echo e($pos ?? 9999); ?>">
                        <?php if($pos): ?> <span class="badge badge-<?php echo e($badge); ?> px-2"><?php echo e($pos); ?></span>
                        <?php else: ?> <span class="text-muted">—</span> <?php endif; ?>
                    </td>
                    <td class="text-center" data-val="<?php echo e($chg); ?>">
                        <?php if($chg > 0): ?> <span class="change-up">▲ +<?php echo e($chg); ?></span>
                        <?php elseif($chg < 0): ?> <span class="change-down">▼ <?php echo e($chg); ?></span>
                        <?php else: ?> <span class="change-none">—</span> <?php endif; ?>
                    </td>
                    <td class="text-center text-muted" data-val="<?php echo e($kw->search_volume ?? 0); ?>">
                        <?php echo e(($kw->search_volume ?? 0) > 0 ? number_format($kw->search_volume) : '—'); ?>

                    </td>
                    <td class="text-center text-muted" data-val="<?php echo e($kw->organic_traffic ?? 0); ?>">
                        <?php echo e(($kw->organic_traffic ?? 0) > 0 ? number_format($kw->organic_traffic) : '—'); ?>

                    </td>
                    <td class="text-center" data-val="<?php echo e($kdVal ?? 999); ?>">
                        <?php if($kdVal !== null): ?>
                            <span class="badge badge-<?php echo e($kdVal <= 30 ? 'success' : ($kdVal <= 60 ? 'warning' : 'danger')); ?>"><?php echo e($kdVal); ?></span>
                        <?php else: ?> <span class="text-muted">—</span> <?php endif; ?>
                    </td>
                    <td class="text-truncate" style="max-width:180px;" data-val="<?php echo e($urlPath ?? ''); ?>">
                        <?php if($urlPath): ?>
                            <a href="<?php echo e($kw->target_url); ?>" target="_blank" rel="noopener" class="text-info small" title="<?php echo e($kw->target_url); ?>"><?php echo e($urlPath); ?></a>
                        <?php else: ?> <span class="text-muted">—</span> <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php if($competitorData): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flag-checkered mr-2 text-warning"></i>Competitor Comparison
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">

                
                <?php if(!empty($competitorData['domains'])): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered table-hover mb-0 tbl-sort" style="font-size:.85rem;">
                        <thead class="thead-light">
                            <tr>
                                <th class="sortable" data-type="text">Domain <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Total KW <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Avg Pos <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Top 3 <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Top 10 <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Top 20 <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Top 50 <span class="sort-icon"></span></th>
                                <th class="sortable text-center" data-type="num">Visibility <span class="sort-icon"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $competitorData['domains']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr <?php echo e(isset($domData['is_main']) && $domData['is_main'] ? 'class=table-primary font-weight-bold' : ''); ?>>
                                <td data-val="<?php echo e($domData['domain'] ?? ''); ?>">
                                    <?php if(isset($domData['is_main']) && $domData['is_main']): ?>
                                        <i class="fas fa-home mr-1 text-primary"></i>
                                    <?php else: ?>
                                        <i class="fas fa-flag mr-1 text-warning"></i>
                                    <?php endif; ?>
                                    <?php echo e($domData['domain'] ?? '—'); ?>

                                </td>
                                <td class="text-center" data-val="<?php echo e($domData['total_keywords'] ?? 0); ?>"><?php echo e(number_format($domData['total_keywords'] ?? 0)); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['avg_position'] ?? 9999); ?>"><?php echo e($domData['avg_position'] ?? '—'); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['top_3'] ?? 0); ?>"><?php echo e(number_format($domData['top_3'] ?? 0)); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['top_10'] ?? 0); ?>"><?php echo e(number_format($domData['top_10'] ?? 0)); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['top_20'] ?? 0); ?>"><?php echo e(number_format($domData['top_20'] ?? 0)); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['top_50'] ?? 0); ?>"><?php echo e(number_format($domData['top_50'] ?? 0)); ?></td>
                                <td class="text-center" data-val="<?php echo e($domData['visibility_score'] ?? 0); ?>"><?php echo e(number_format($domData['visibility_score'] ?? 0, 2)); ?>%</td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-chart-bar mr-1"></i>Visibility Score
                        </h6>
                        <div class="comp-chart-wrap">
                            <canvas id="compVisChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-5 mb-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-chart-pie mr-1"></i>Share of Voice (Top 10)
                        </h6>
                        <div class="comp-chart-wrap">
                            <canvas id="compSovChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(count($landingPages)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2 text-info"></i>Top Landing Pages
                    <span class="badge badge-info ml-1">Top 10</span>
                </h3>
                <button type="button" class="btn btn-sm btn-outline-info"
                        id="btnAllUrls"
                        data-snapshot="<?php echo e($selectedSnapshot->id); ?>">
                    <i class="fas fa-list mr-1"></i>Xem toàn bộ URL
                </button>
            </div>
            <div class="card-body p-0">
                <div style="overflow-x:auto;">
                <table class="table table-sm table-hover mb-0 tbl-sort" style="font-size:.85rem;">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-type="num" style="width:40px;"># <span class="sort-icon"></span></th>
                            <th class="sortable" data-type="text">URL <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num">Số Keyword <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num">Vị trí tốt nhất <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num">Avg Pos <span class="sort-icon"></span></th>
                            <th class="sortable text-center" data-type="num">Total Volume <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $landingPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $pagePath = preg_replace('/^https?:\/\/[^\/]+/', '', $page->target_url ?? $page['target_url'] ?? '') ?: '/';
                            $pageUrl  = $page->target_url ?? $page['target_url'] ?? '';
                            $kwCount  = $page->kw_count  ?? $page['kw_count']  ?? 0;
                            $bestPos  = $page->best_pos  ?? $page['best_pos']  ?? null;
                            $avgPos   = $page->avg_pos   ?? $page['avg_pos']   ?? null;
                            $volume   = $page->total_volume ?? $page['total_volume'] ?? 0;
                        ?>
                        <tr>
                            <td class="text-muted" data-val="<?php echo e($i + 1); ?>"><?php echo e($i + 1); ?></td>
                            <td class="text-truncate" style="max-width:320px;" title="<?php echo e($pageUrl); ?>" data-val="<?php echo e($pagePath); ?>">
                                <a href="<?php echo e($pageUrl); ?>" target="_blank" rel="noopener" class="text-info">
                                    <?php echo e($pagePath); ?>

                                </a>
                            </td>
                            <td class="text-center" data-val="<?php echo e($kwCount); ?>">
                                <button type="button"
                                        class="btn btn-xs btn-outline-primary btn-url-kw"
                                        data-snapshot="<?php echo e($selectedSnapshot->id); ?>"
                                        data-url="<?php echo e($pageUrl); ?>">
                                    <?php echo e($kwCount); ?>

                                </button>
                            </td>
                            <td class="text-center" data-val="<?php echo e($bestPos ?? 9999); ?>">
                                <?php if($bestPos): ?>
                                    <?php $bBadge = $bestPos <= 3 ? 'success' : ($bestPos <= 10 ? 'info' : ($bestPos <= 20 ? 'primary' : ($bestPos <= 50 ? 'warning' : 'secondary'))); ?>
                                    <span class="badge badge-<?php echo e($bBadge); ?>"><?php echo e($bestPos); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-muted" data-val="<?php echo e($avgPos ?? 9999); ?>"><?php echo e($avgPos ?? '—'); ?></td>
                            <td class="text-center text-muted" data-val="<?php echo e($volume); ?>"><?php echo e(number_format($volume)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?> 
<?php endif; ?> 





<div class="modal fade" id="allUrlsModal" tabindex="-1" role="dialog" aria-labelledby="allUrlsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="allUrlsModalLabel">
                    <i class="fas fa-list mr-2"></i>Toàn bộ Landing Pages
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2 pb-1">
                
                <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                    <input type="text" id="urlSearchInput" class="form-control form-control-sm"
                           placeholder="Tìm URL..." style="max-width:280px;">
                    <button type="button" id="btnCheckAllUrls" class="btn btn-xs btn-outline-secondary ml-2">
                        <i class="fas fa-check-square mr-1"></i>Chọn tất cả
                    </button>
                    <button type="button" id="btnUncheckAllUrls" class="btn btn-xs btn-outline-secondary ml-1">
                        <i class="fas fa-square mr-1"></i>Bỏ chọn
                    </button>
                    <span id="urlSelectedCount" class="badge badge-primary ml-2"></span>
                </div>
                
                <div style="max-height:60vh; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size:.83rem;">
                        <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="width:36px;"></th>
                                <th style="width:40px;">#</th>
                                <th>URL</th>
                                <th class="text-center" style="width:90px;">Số Keyword</th>
                                <th class="text-center" style="width:90px;">Vị trí tốt</th>
                                <th class="text-center" style="width:70px;">Avg</th>
                                <th class="text-center" style="width:100px;">Volume</th>
                            </tr>
                        </thead>
                        <tbody id="allUrlsBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i>Nhấn "Xem toàn bộ URL" để tải dữ liệu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="text-muted small" id="allUrlsCount"></span>
                </div>
                <div>
                    <button type="button" class="btn btn-success" id="btnReportFromUrls">
                        <i class="fas fa-file-pdf mr-1"></i>Tạo báo cáo URL đã chọn
                    </button>
                    <button type="button" class="btn btn-secondary ml-1" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="urlKeywordsModal" tabindex="-1" role="dialog" aria-labelledby="urlKeywordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="urlKeywordsModalLabel">
                    <i class="fas fa-key mr-2"></i>Keywords — <span id="urlKwLabel" class="text-truncate" style="max-width:340px;"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2 pb-1">
                
                <div class="d-flex align-items-center mb-2 flex-wrap">
                    <button type="button" id="btnCheckAllKw" class="btn btn-xs btn-outline-secondary mr-1">
                        <i class="fas fa-check-square mr-1"></i>Chọn tất cả
                    </button>
                    <button type="button" id="btnUncheckAllKw" class="btn btn-xs btn-outline-secondary">
                        <i class="fas fa-square mr-1"></i>Bỏ chọn
                    </button>
                    <span id="kwSelectedCount" class="badge badge-primary ml-2"></span>
                </div>
                
                <div style="max-height:55vh; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size:.83rem;">
                        <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Keyword</th>
                                <th class="text-center" style="width:80px;">Vị trí</th>
                                <th class="text-center" style="width:90px;">Thay đổi</th>
                                <th class="text-center" style="width:100px;">Volume</th>
                            </tr>
                        </thead>
                        <tbody id="urlKwBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i>Chọn một URL để xem keywords.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="text-muted small" id="urlKwCount"></span>
                </div>
                <div>
                    <button type="button" class="btn btn-success" id="btnReportFromKw">
                        <i class="fas fa-file-pdf mr-1"></i>Tạo báo cáo Keyword đã chọn
                    </button>
                    <button type="button" class="btn btn-secondary ml-1" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="filteredReportModal" tabindex="-1" role="dialog" aria-labelledby="filteredReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="filteredReportModalLabel">
                    <i class="fas fa-file-pdf mr-2"></i>Tạo báo cáo Filtered
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="<?php echo e(route('reports.store-filtered')); ?>">
                <?php echo csrf_field(); ?>
                
                <input type="hidden" name="project_id"  id="frProjectId"
                       value="<?php echo e($selectedProject?->id ?? ''); ?>">
                <input type="hidden" name="snapshot_id" id="frSnapshotId"
                       value="<?php echo e($selectedSnapshot?->id ?? ''); ?>">
                <input type="hidden" name="filter_type" id="frFilterType" value="">

                
                <div id="frHiddenInputs"></div>

                <div class="modal-body">

                    
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="frFilterSummary">—</span>
                    </div>

                    
                    <div class="form-group">
                        <label for="frTitle" class="font-weight-bold">
                            Tiêu đề báo cáo <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="report_title" id="frTitle"
                               placeholder="VD: Báo cáo SEO tháng 4/2026"
                               value="<?php echo e($selectedProject ? $selectedProject->name . ' — ' . now()->format('m/Y') : ''); ?>"
                               required>
                    </div>

                    
                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                   id="frIncludeComp" name="include_competitors" value="1">
                            <label class="custom-control-label font-weight-bold" for="frIncludeComp">
                                Bao gồm phân tích đối thủ
                            </label>
                        </div>
                    </div>

                    
                    <div id="frCompetitorList" style="display:none;" class="pl-3 border-left border-info">
                        <?php if($selectedProject && $availableCompetitors->count()): ?>
                            <small class="text-muted d-block mb-2">Chọn đối thủ để so sánh:</small>
                            <?php $__currentLoopData = $availableCompetitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                       id="frComp_<?php echo e($comp->id); ?>"
                                       name="competitor_ids[]"
                                       value="<?php echo e($comp->id); ?>"
                                       <?php echo e(in_array($comp->id, $selectedCompetitorIds) ? 'checked' : ''); ?>>
                                <label class="custom-control-label small" for="frComp_<?php echo e($comp->id); ?>">
                                    <?php echo e($comp->name); ?>

                                    <?php if($comp->domain): ?>
                                        <span class="text-muted">(<?php echo e($comp->domain); ?>)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <small class="text-muted">Không có đối thủ nào được cấu hình.</small>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-pdf mr-1"></i>Tạo PDF ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>

<script>
(function () {
    function getVal(td, type) {
        const v = td.dataset.val;
        if (v !== undefined) return type === 'num' ? parseFloat(v) : v.toLowerCase();
        const t = td.textContent.replace(/[▲▼+,\s%]/g, '').trim();
        return type === 'num' ? (parseFloat(t) || 0) : t.toLowerCase();
    }

    document.querySelectorAll('table.tbl-sort').forEach(table => {
        const ths = table.querySelectorAll('thead th.sortable');
        ths.forEach((th, colIdx) => {
            th.addEventListener('click', () => {
                const type    = th.dataset.type || 'text';
                const wasAsc  = th.classList.contains('asc');
                const sortDir = wasAsc ? 'desc' : 'asc';

                // Reset tất cả headers
                ths.forEach(h => h.classList.remove('asc', 'desc'));
                th.classList.add(sortDir);

                const tbody = table.querySelector('tbody');
                const rows  = [...tbody.querySelectorAll('tr')];

                rows.sort((a, b) => {
                    const tdA = a.querySelectorAll('td')[colIdx];
                    const tdB = b.querySelectorAll('td')[colIdx];
                    if (!tdA || !tdB) return 0;
                    const vA = getVal(tdA, type);
                    const vB = getVal(tdB, type);
                    if (vA < vB) return sortDir === 'asc' ? -1 : 1;
                    if (vA > vB) return sortDir === 'asc' ? 1 : -1;
                    return 0;
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        });
    });
})();
</script>

<script>
// ════════════════════════════════════════════════════════════════════════════════
// Script 1 · Filter auto-submit logic
// ════════════════════════════════════════════════════════════════════════════════
(function () {
    const clientSel   = document.getElementById('clientSelect');
    const projectSel  = document.getElementById('projectSelect');
    const snapshotSel = document.getElementById('snapshotSelect');
    const dateRange   = document.getElementById('dateRangeSelect');
    const form        = document.getElementById('filterForm');

    // Client → AJAX load projects → submit
    if (clientSel) {
        clientSel.addEventListener('change', function () {
            const clientId = this.value;

            // Reset downstream selects
            if (projectSel) {
                projectSel.innerHTML = '<option value="">-- Chọn Project --</option>';
                projectSel.disabled  = true;
            }
            if (snapshotSel) {
                snapshotSel.innerHTML = '<option value="">-- Mới nhất --</option>';
                snapshotSel.disabled  = true;
            }

            if (!clientId) { form.submit(); return; }

            if (projectSel) projectSel.innerHTML = '<option value="">Đang tải...</option>';

            fetch(`/api/projects-by-client/${clientId}`)
                .then(r => r.json())
                .then(data => {
                    if (projectSel) {
                        projectSel.innerHTML = '<option value="">-- Chọn Project --</option>';
                        data.forEach(p => {
                            projectSel.add(new Option(p.name, p.id));
                        });
                        projectSel.disabled = false;
                    }
                    form.submit();
                })
                .catch(() => {
                    if (projectSel) {
                        projectSel.innerHTML = '<option value="">-- Chọn Project --</option>';
                        projectSel.disabled  = false;
                    }
                    form.submit();
                });
        });
    }

    // Project → AJAX load snapshots → submit
    if (projectSel) {
        projectSel.addEventListener('change', function () {
            const projectId = this.value;

            if (snapshotSel) {
                snapshotSel.innerHTML = '<option value="">-- Mới nhất --</option>';
                snapshotSel.disabled  = true;
            }

            if (!projectId) { form.submit(); return; }

            if (snapshotSel) {
                snapshotSel.innerHTML = '<option value="">Đang tải...</option>';
            }

            fetch(`/api/snapshots-by-project/${projectId}`)
                .then(r => r.json())
                .then(data => {
                    if (snapshotSel) {
                        snapshotSel.innerHTML = '<option value="">-- Mới nhất --</option>';
                        data.forEach(s => {
                            const d = new Date(s.report_date).toLocaleDateString('vi-VN');
                            snapshotSel.add(new Option(d, s.id));
                        });
                        snapshotSel.disabled = false;
                    }
                    form.submit();
                })
                .catch(() => {
                    if (snapshotSel) {
                        snapshotSel.innerHTML = '<option value="">-- Mới nhất --</option>';
                        snapshotSel.disabled  = false;
                    }
                    form.submit();
                });
        });
    }

    // Snapshot / date_range → submit ngay
    if (snapshotSel) {
        snapshotSel.addEventListener('change', () => form.submit());
    }
    if (dateRange) {
        dateRange.addEventListener('change', () => form.submit());
    }
})();
</script>


<?php if($timelineData && !empty($timelineData['labels'])): ?>
<script>
(function () {
    const ctx = document.getElementById('timelineChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($timelineData['labels'], 15, 512) ?>,
            datasets: [{
                label: 'Avg Position',
                data: <?php echo json_encode($timelineData['values'] ?? $timelineData['data'] ?? [], 15, 512) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,0.08)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#4e73df',
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    reverse: true,
                    title: { display: true, text: 'Position (1 = best)' },
                    min: 1,
                    ticks: { stepSize: 5 }
                },
                x: {
                    ticks: { maxRotation: 45, minRotation: 0 }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.raw ? `Vị trí: ${ctx.raw}` : 'Không có dữ liệu'
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php if(isset($kpis['position_distribution']) && !empty($kpis['position_distribution'])): ?>
<script>
(function () {
    const ctx = document.getElementById('distChart');
    if (!ctx) return;

    const dist   = <?php echo json_encode($kpis['position_distribution'], 15, 512) ?>;
    const labels = Object.keys(dist);
    const values = Object.values(dist);

    const colors = ['#28a745','#17a2b8','#4e73df','#6f42c1','#ffc107','#6c757d','#343a40'];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Số keyword',
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.raw} keywords`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Số keyword' }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>


<?php if($competitorData): ?>
<script>
(function () {
    const domains = <?php echo json_encode($competitorData['domains'] ?? [], 15, 512) ?>;

    // ── Visibility Bar Chart ──────────────────────────────────────────
    const ctxVis = document.getElementById('compVisChart');
    if (ctxVis && domains.length) {
        new Chart(ctxVis, {
            type: 'bar',
            data: {
                labels: domains.map(d => d.domain ?? d.name ?? ''),
                datasets: [{
                    label: 'Visibility Score (%)',
                    data: domains.map(d => parseFloat(d.visibility_score ?? 0)),
                    backgroundColor: domains.map((d, i) =>
                        (d.is_main ? '#4e73df' : ['#e74a3b','#f6c23e','#1cc88a','#36b9cc','#858796'][i % 5])
                    ),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `Visibility: ${ctx.raw}%`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: '%' } }
                }
            }
        });
    }

    // ── SoV Doughnut Chart ────────────────────────────────────────────
    const ctxSov = document.getElementById('compSovChart');
    if (ctxSov && domains.length) {
        const sovData = domains.map(d => parseInt(d.top_10 ?? 0));
        const total   = sovData.reduce((a, b) => a + b, 0);
        const sovPct  = total > 0 ? sovData.map(v => parseFloat((v / total * 100).toFixed(1))) : sovData;

        new Chart(ctxSov, {
            type: 'doughnut',
            data: {
                labels: domains.map(d => d.domain ?? d.name ?? ''),
                datasets: [{
                    data: sovPct,
                    backgroundColor: ['#4e73df','#e74a3b','#f6c23e','#1cc88a','#36b9cc','#858796'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: ${ctx.raw}%`
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
})();
</script>
<?php endif; ?>


<script>
// ── Modal: Toàn bộ URL ────────────────────────────────────────────────────────
const btnAllUrls = document.getElementById('btnAllUrls');
if (btnAllUrls) {
    btnAllUrls.addEventListener('click', function () {
        const snapshotId = this.dataset.snapshot;
        const tbody      = document.getElementById('allUrlsBody');
        const countEl    = document.getElementById('allUrlsCount');
        tbody.innerHTML  = '<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-info"></i></td></tr>';
        countEl.textContent = '';
        updateUrlSelectedCount();
        $('#allUrlsModal').modal('show');

        fetch(`/api/landing-pages?snapshot_id=${snapshotId}`)
            .then(r => r.json())
            .then(data => {
                countEl.textContent = `${data.length} URLs`;
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map((p, i) => {
                    const path = p.target_url.replace(/^https?:\/\/[^\/]+/, '') || '/';
                    return `<tr>
                        <td class="text-center"><input type="checkbox" class="chk-url" value="${encodeURIComponent(p.target_url)}"></td>
                        <td class="text-muted">${i+1}</td>
                        <td class="text-truncate" style="max-width:320px;" title="${p.target_url}">
                            <a href="${p.target_url}" target="_blank" rel="noopener" class="text-info">${path}</a>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-outline-primary btn-url-kw"
                                    data-snapshot="${snapshotId}" data-url="${p.target_url}">
                                ${p.kw_count}
                            </button>
                        </td>
                        <td class="text-center">${p.best_pos ?? '—'}</td>
                        <td class="text-center">${p.avg_pos ?? '—'}</td>
                        <td class="text-center">${Number(p.total_volume).toLocaleString('vi-VN')}</td>
                    </tr>`;
                }).join('');
                attachUrlKwHandlers(tbody);
                tbody.querySelectorAll('.chk-url').forEach(chk =>
                    chk.addEventListener('change', updateUrlSelectedCount)
                );
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="7" class="text-danger text-center py-3">Lỗi tải dữ liệu</td></tr>';
            });
    });

    document.getElementById('urlSearchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#allUrlsBody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    document.getElementById('btnCheckAllUrls').addEventListener('click', () => {
        document.querySelectorAll('#allUrlsBody .chk-url').forEach(c => c.checked = true);
        updateUrlSelectedCount();
    });
    document.getElementById('btnUncheckAllUrls').addEventListener('click', () => {
        document.querySelectorAll('#allUrlsBody .chk-url').forEach(c => c.checked = false);
        updateUrlSelectedCount();
    });
}

function updateUrlSelectedCount() {
    const n  = document.querySelectorAll('#allUrlsBody .chk-url:checked').length;
    const el = document.getElementById('urlSelectedCount');
    if (el) el.textContent = n > 0 ? `${n} URL đã chọn` : '';
}

document.getElementById('btnReportFromUrls')?.addEventListener('click', function () {
    const checked = [...document.querySelectorAll('#allUrlsBody .chk-url:checked')];
    if (!checked.length) { alert('Vui lòng chọn ít nhất 1 URL.'); return; }
    openFilteredReportModal('urls', checked.map(c => decodeURIComponent(c.value)), [], `${checked.length} URL đã chọn`);
});

// ── Modal: Keywords của URL ───────────────────────────────────────────────────
function updateKwSelectedCount() {
    const n  = document.querySelectorAll('#urlKwBody .chk-kw:checked').length;
    const el = document.getElementById('kwSelectedCount');
    if (el) el.textContent = n > 0 ? `${n} keyword đã chọn` : '';
}
document.getElementById('btnCheckAllKw')?.addEventListener('click', () => {
    document.querySelectorAll('#urlKwBody .chk-kw').forEach(c => c.checked = true);
    updateKwSelectedCount();
});
document.getElementById('btnUncheckAllKw')?.addEventListener('click', () => {
    document.querySelectorAll('#urlKwBody .chk-kw').forEach(c => c.checked = false);
    updateKwSelectedCount();
});
document.getElementById('btnReportFromKw')?.addEventListener('click', function () {
    const checked = [...document.querySelectorAll('#urlKwBody .chk-kw:checked')];
    if (!checked.length) { alert('Vui lòng chọn ít nhất 1 keyword.'); return; }
    openFilteredReportModal('keywords', [], checked.map(c => parseInt(c.value)), `${checked.length} keyword đã chọn`);
});

function attachUrlKwHandlers(container) {
    container.querySelectorAll('.btn-url-kw').forEach(btn => {
        btn.addEventListener('click', function () {
            const snapshotId = this.dataset.snapshot;
            const url        = this.dataset.url;
            const tbody      = document.getElementById('urlKwBody');
            const countEl    = document.getElementById('urlKwCount');
            const label      = document.getElementById('urlKwLabel');

            label.textContent   = url.replace(/^https?:\/\/[^\/]+/, '') || '/';
            tbody.innerHTML     = '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>';
            countEl.textContent = '';
            updateKwSelectedCount();
            $('#urlKeywordsModal').modal('show');

            fetch(`/api/url-keywords?snapshot_id=${snapshotId}&url=${encodeURIComponent(url)}`)
                .then(r => r.json())
                .then(data => {
                    countEl.textContent = `${data.length} keywords`;
                    if (!data.length) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.map(kw => {
                        const pos   = kw.current_position ?? '—';
                        const badge = kw.current_position <= 3  ? 'success'
                                    : kw.current_position <= 10 ? 'info'
                                    : kw.current_position <= 20 ? 'primary'
                                    : kw.current_position <= 50 ? 'warning' : 'secondary';
                        const chg   = kw.position_change > 0
                                    ? `<span class="change-up">▲ +${kw.position_change}</span>`
                                    : kw.position_change < 0
                                        ? `<span class="change-down">▼ ${kw.position_change}</span>`
                                        : '<span class="change-none">—</span>';
                        const kwId  = kw.keyword_id ?? 0;
                        return `<tr>
                            <td class="text-center"><input type="checkbox" class="chk-kw" value="${kwId}" checked></td>
                            <td>${kw.keyword}</td>
                            <td class="text-center"><span class="badge badge-${badge} px-2">${pos}</span></td>
                            <td class="text-center">${chg}</td>
                            <td class="text-center text-muted">${kw.search_volume > 0 ? Number(kw.search_volume).toLocaleString('vi-VN') : '—'}</td>
                        </tr>`;
                    }).join('');
                    tbody.querySelectorAll('.chk-kw').forEach(chk =>
                        chk.addEventListener('change', updateKwSelectedCount)
                    );
                    updateKwSelectedCount();
                })
                .catch(() => {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center py-3">Lỗi tải dữ liệu</td></tr>';
                });
        });
    });
}

attachUrlKwHandlers(document);

// ── Mở modal cấu hình báo cáo filtered ───────────────────────────────────────
function openFilteredReportModal(filterType, urls, keywordIds, summary) {
    document.getElementById('frFilterType').value = filterType;
    document.getElementById('frFilterSummary').textContent = 'Báo cáo cho: ' + summary;

    const container = document.getElementById('frHiddenInputs');
    container.innerHTML = '';
    if (filterType === 'urls') {
        urls.forEach(u => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'selected_urls[]'; inp.value = u;
            container.appendChild(inp);
        });
    } else {
        keywordIds.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'selected_keyword_ids[]'; inp.value = id;
            container.appendChild(inp);
        });
    }

    $('#allUrlsModal').modal('hide');
    $('#urlKeywordsModal').modal('hide');
    setTimeout(() => $('#filteredReportModal').modal('show'), 350);
}

document.getElementById('frIncludeComp')?.addEventListener('change', function () {
    document.getElementById('frCompetitorList').style.display = this.checked ? 'block' : 'none';
});

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/dashboard/index.blade.php ENDPATH**/ ?>