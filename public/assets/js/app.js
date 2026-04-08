/**
 * RankReport Pro — App JS
 * Global scripts used across all pages.
 */
(function ($) {
    'use strict';

    // ── Auto-dismiss flash alerts after 5s ─────────────────────────────────
    setTimeout(function () {
        $('.alert.alert-success, .alert.alert-info').fadeTo(500, 0).slideUp(300, function () {
            $(this).remove();
        });
    }, 5000);

    // ── File input custom label ────────────────────────────────────────────
    $(document).on('change', '.custom-file-input', function () {
        const fileName = this.files[0]?.name || 'Chọn file';
        $(this).next('.custom-file-label').text(fileName);
    });

    // ── Confirm delete form (global) ───────────────────────────────────────
    // Already handled via onsubmit inline, but provide fallback
    $(document).on('submit', 'form[data-confirm]', function (e) {
        const msg = $(this).data('confirm') || 'Bạn có chắc chắn?';
        if (!window.confirm(msg)) {
            e.preventDefault();
        }
    });

    // ── Chart.js global defaults ───────────────────────────────────────────
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Source Sans 3', 'Source Sans Pro', sans-serif";
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = '#6c757d';
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.plugins.tooltip.mode    = 'index';
        Chart.defaults.plugins.tooltip.intersect = false;
    }

    // ── Destroy existing Chart before creating new (to avoid canvas reuse) ─
    window.createChart = function (canvasId, config) {
        const existing = Chart.getChart(canvasId);
        if (existing) existing.destroy();
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        return new Chart(canvas, config);
    };

    // ── Tooltip for truncated text ─────────────────────────────────────────
    $('[title]').tooltip({ trigger: 'hover', placement: 'top' });

    // ── Load projects via AJAX when client selector changes ───────────────
    $(document).on('change', '[data-ajax-projects]', function () {
        const clientId   = $(this).val();
        const targetSel  = $(this).data('ajax-projects');
        if (!clientId) return;
        $.getJSON('/api/projects-by-client/' + clientId, function (data) {
            const $sel = $(targetSel).empty().append('<option value="">-- Chọn Project --</option>');
            data.forEach(function (p) {
                $sel.append(`<option value="${p.id}">${p.name} (${p.domain})</option>`);
            });
        });
    });

    // ── Load snapshots via AJAX when project selector changes ─────────────
    $(document).on('change', '[data-ajax-snapshots]', function () {
        const projectId  = $(this).val();
        const targetSel  = $(this).data('ajax-snapshots');
        if (!projectId) return;
        $.getJSON('/api/snapshots-by-project/' + projectId, function (data) {
            const $sel = $(targetSel).empty().append('<option value="">-- Mới nhất --</option>');
            data.forEach(function (s) {
                $sel.append(`<option value="${s.id}">${s.report_date} (${s.total_keywords.toLocaleString()} kw)</option>`);
            });
        });
    });

})(jQuery);
