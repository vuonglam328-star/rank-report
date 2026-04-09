<?php $__env->startSection('title', 'Keywords'); ?>
<?php $__env->startSection('page-title', 'Keyword Rankings'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Keywords</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.change-up   { color: #28a745; font-weight: 600; }
.change-down { color: #dc3545; font-weight: 600; }
.change-none { color: #6c757d; }
th.sortable { cursor:pointer; user-select:none; white-space:nowrap; }
th.sortable:hover { background:rgba(0,0,0,.04); }
th.sortable .sort-icon {
    display:inline-block; width:1em; text-align:center;
    font-style:normal; font-size:.75em; margin-left:2px;
    color:#adb5bd;
}
th.sortable .sort-icon::after        { content:'⇅'; }
th.sortable.asc  .sort-icon::after   { content:'▲'; color:#007bff; }
th.sortable.desc .sort-icon::after   { content:'▼'; color:#007bff; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" action="<?php echo e(route('keywords.index')); ?>" id="filterForm">
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="mb-1 small">Project</label>
                <select name="project_id" class="form-control form-control-sm" id="projSelect">
                    <option value="">-- Chọn Project --</option>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php echo e(request('project_id') == $p->id ? 'selected' : ''); ?>>
                            <?php echo e($p->name); ?> (<?php echo e($p->domain_clean); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="mb-1 small">Snapshot</label>
                <select name="snapshot_id" class="form-control form-control-sm">
                    <option value="">-- Mới nhất --</option>
                    <?php $__currentLoopData = $snapshots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php echo e(request('snapshot_id') == $s->id || ($selectedSnapshot && $selectedSnapshot->id == $s->id) ? 'selected' : ''); ?>>
                            <?php echo e($s->report_date->format('d/m/Y')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="mb-1 small">Nhóm vị trí</label>
                <select name="position_group" class="form-control form-control-sm">
                    <option value="">Tất cả</option>
                    <option value="top_3"   <?php echo e(request('position_group') === 'top_3'   ? 'selected' : ''); ?>>Top 3</option>
                    <option value="top_10"  <?php echo e(request('position_group') === 'top_10'  ? 'selected' : ''); ?>>Top 10</option>
                    <option value="top_20"  <?php echo e(request('position_group') === 'top_20'  ? 'selected' : ''); ?>>Top 20</option>
                    <option value="top_50"  <?php echo e(request('position_group') === 'top_50'  ? 'selected' : ''); ?>>Top 50</option>
                    <option value="top_100" <?php echo e(request('position_group') === 'top_100' ? 'selected' : ''); ?>>Top 100</option>
                    <option value="outside" <?php echo e(request('position_group') === 'outside' ? 'selected' : ''); ?>>Ngoài Top 100</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="mb-1 small">Tìm kiếm</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       value="<?php echo e(request('search')); ?>" placeholder="Nhập keyword...">
            </div>
            <div class="col-md-1">
                <label class="mb-1 small">Brand</label>
                <select name="brand" class="form-control form-control-sm">
                    <option value="all">Tất cả</option>
                    <option value="branded"     <?php echo e(request('brand') === 'branded'     ? 'selected' : ''); ?>>Branded</option>
                    <option value="non_branded" <?php echo e(request('brand') === 'non_branded' ? 'selected' : ''); ?>>Non-Branded</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-filter mr-1"></i>Lọc
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<?php if(!$selectedSnapshot): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
    <h5>Chọn project để xem keyword rankings</h5>
</div>
<?php else: ?>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-search mr-2"></i>
            Keywords — <?php echo e($selectedProject->name); ?>

            <span class="badge badge-secondary ml-2"><?php echo e($keywords->total()); ?> kết quả</span>
        </h3>
        <small class="text-muted">
            Snapshot: <?php echo e($selectedSnapshot->report_date->format('d/m/Y')); ?>

            <?php if($keywords->hasPages()): ?>
                &nbsp;·&nbsp; <span class="text-warning">Sort áp dụng trong trang hiện tại</span>
            <?php endif; ?>
        </small>
    </div>
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="table table-sm table-hover mb-0 tbl-sort" style="font-size:.85rem;">
            <thead class="thead-light">
                <tr>
                    <th class="sortable" data-type="text">Keyword <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num" style="width:80px;">Vị trí <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num" style="width:90px;">Thay đổi <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="num" style="width:90px;">Volume <span class="sort-icon"></span></th>
                    <th class="sortable" data-type="text">Landing Page <span class="sort-icon"></span></th>
                    <th class="sortable text-center" data-type="text" style="width:70px;">Brand <span class="sort-icon"></span></th>
                    <th class="text-center" style="width:70px;">Timeline</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td data-val="<?php echo e($row->keyword); ?>">
                        <span title="<?php echo e($row->keyword); ?>"><?php echo e(Str::limit($row->keyword, 60)); ?></span>
                        <?php if($row->tag): ?>
                            <span class="badge badge-light border ml-1"><?php echo e($row->tag); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" data-val="<?php echo e($row->current_position ?? 9999); ?>">
                        <?php if($row->current_position): ?>
                            <?php
                                $badge = $row->current_position <= 3 ? 'success' : ($row->current_position <= 10 ? 'info' : ($row->current_position <= 20 ? 'primary' : ($row->current_position <= 50 ? 'warning' : 'secondary')));
                            ?>
                            <span class="badge badge-<?php echo e($badge); ?> px-2"><?php echo e($row->current_position); ?></span>
                        <?php else: ?>
                            <span class="badge badge-light text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" data-val="<?php echo e($row->position_change ?? 0); ?>">
                        <?php if($row->position_change > 0): ?>
                            <span class="change-up">▲ +<?php echo e($row->position_change); ?></span>
                        <?php elseif($row->position_change < 0): ?>
                            <span class="change-down">▼ <?php echo e($row->position_change); ?></span>
                        <?php else: ?>
                            <span class="change-none">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-muted" data-val="<?php echo e($row->search_volume ?? 0); ?>">
                        <?php echo e($row->search_volume > 0 ? number_format($row->search_volume) : '—'); ?>

                    </td>
                    <td class="text-truncate" style="max-width:220px;" title="<?php echo e($row->target_url); ?>"
                        data-val="<?php echo e($row->target_url ? preg_replace('/^https?:\/\/[^\/]+/', '', $row->target_url) : ''); ?>">
                        <?php if($row->target_url): ?>
                            <a href="<?php echo e($row->target_url); ?>" target="_blank" rel="noopener" class="text-info small">
                                <?php echo e(preg_replace('/^https?:\/\/[^\/]+/', '', $row->target_url) ?: '/'); ?>

                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" data-val="<?php echo e($row->brand_flag ? '1' : '0'); ?>">
                        <?php if($row->brand_flag): ?>
                            <span class="badge badge-warning">B</span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-info btn-timeline"
                                data-keyword-id="<?php echo e($row->keyword_id); ?>"
                                data-keyword="<?php echo e($row->keyword); ?>">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>
                        Không có keyword nào khớp với bộ lọc.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($keywords->hasPages()): ?>
    <div class="card-footer">
        <?php echo e($keywords->links('pagination::bootstrap-4')); ?>

    </div>
    <?php endif; ?>
</div>
<?php endif; ?>


<div class="modal fade" id="timelineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line mr-2"></i>Timeline: <span id="timelineKwLabel"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <canvas id="keywordTimelineChart" height="120"></canvas>
                <div id="timelineLoading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ── Table Sort ────────────────────────────────────────────────────────────────
(function () {
    function getVal(td, type) {
        const v = td.dataset.val;
        if (v !== undefined) return type === 'num' ? parseFloat(v) : v.toLowerCase();
        const t = td.textContent.replace(/[▲▼+,\s%]/g, '').trim();
        return type === 'num' ? (parseFloat(t) || 0) : t.toLowerCase();
    }
    document.querySelectorAll('table.tbl-sort').forEach(table => {
        table.querySelectorAll('thead th.sortable').forEach((th, colIdx) => {
            th.addEventListener('click', () => {
                const type   = th.dataset.type || 'text';
                const dir    = th.classList.contains('asc') ? 'desc' : 'asc';
                table.querySelectorAll('thead th.sortable').forEach(h => h.classList.remove('asc','desc'));
                th.classList.add(dir);
                const tbody = table.querySelector('tbody');
                [...tbody.querySelectorAll('tr')].sort((a, b) => {
                    const vA = getVal(a.querySelectorAll('td')[colIdx], type);
                    const vB = getVal(b.querySelectorAll('td')[colIdx], type);
                    return (vA < vB ? -1 : vA > vB ? 1 : 0) * (dir === 'asc' ? 1 : -1);
                }).forEach(r => tbody.appendChild(r));
            });
        });
    });
})();

// ── Auto-submit khi đổi Project: load snapshots → submit ─────────────────────
document.getElementById('projSelect').addEventListener('change', function () {
    const projectId = this.value;
    const snapSel   = document.querySelector('select[name="snapshot_id"]');

    if (!projectId) {
        snapSel.innerHTML = '<option value="">-- Mới nhất --</option>';
        document.getElementById('filterForm').submit();
        return;
    }

    snapSel.innerHTML = '<option value="">Đang tải...</option>';
    snapSel.disabled  = true;

    fetch(`/api/snapshots-by-project/${projectId}`)
        .then(r => r.json())
        .then(data => {
            snapSel.innerHTML = '<option value="">-- Mới nhất --</option>';
            data.forEach(s => {
                const d  = new Date(s.report_date).toLocaleDateString('vi-VN');
                snapSel.add(new Option(d, s.id));
            });
            snapSel.disabled = false;
            // Tự submit để load keywords của project
            document.getElementById('filterForm').submit();
        })
        .catch(() => {
            snapSel.innerHTML = '<option value="">-- Mới nhất --</option>';
            snapSel.disabled  = false;
            document.getElementById('filterForm').submit();
        });
});

// ── Auto-submit khi đổi Snapshot ─────────────────────────────────────────────
document.querySelector('select[name="snapshot_id"]').addEventListener('change', function () {
    document.getElementById('filterForm').submit();
});

// ── Timeline Modal ────────────────────────────────────────────────────────────
let timelineChart = null;

document.querySelectorAll('.btn-timeline').forEach(btn => {
    btn.addEventListener('click', function() {
        const kwId    = this.dataset.keywordId;
        const kwLabel = this.dataset.keyword;

        document.getElementById('timelineKwLabel').textContent = kwLabel;
        document.getElementById('timelineLoading').style.display = 'block';
        document.getElementById('keywordTimelineChart').style.display = 'none';

        $('#timelineModal').modal('show');

        if (timelineChart) { timelineChart.destroy(); timelineChart = null; }

        fetch(`/keywords/${kwId}/timeline`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('timelineLoading').style.display = 'none';
                document.getElementById('keywordTimelineChart').style.display = 'block';

                timelineChart = new Chart(document.getElementById('keywordTimelineChart'), {
                    type: 'line',
                    data: data.chartData,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                reverse: true,
                                title: { display: true, text: 'Position (1 = best)' },
                                min: 1,
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.raw ? `Position: ${ctx.raw}` : 'Not ranked'
                                }
                            }
                        }
                    }
                });
            })
            .catch(() => {
                document.getElementById('timelineLoading').innerHTML = '<p class="text-danger">Lỗi tải dữ liệu</p>';
            });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\rankreport-pro\resources\views/keywords/index.blade.php ENDPATH**/ ?>