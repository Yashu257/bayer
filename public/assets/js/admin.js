(function () {
    'use strict';

    // ── Sidebar toggle (mobile) ────────────────────────────────────────────────
    var sidebar  = document.getElementById('adm-sidebar');
    var overlay  = document.getElementById('adm-overlay');
    var toggle   = document.getElementById('adm-toggle');

    function openSidebar() {
        if (sidebar)  sidebar.classList.add('open');
        if (overlay)  overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar)  sidebar.classList.remove('open');
        if (overlay)  overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (toggle)  toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // ── Auto-dismiss alerts ────────────────────────────────────────────────────
    document.querySelectorAll('.adm-alert-auto').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });

    // ── Confirm destructive actions ────────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ── Search filter (client-side table row filter) ───────────────────────────
    var searchInput = document.getElementById('adm-table-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q     = searchInput.value.toLowerCase().trim();
            var rows  = document.querySelectorAll('.adm-filterable');
            var shown = 0;

            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                var match = !q || text.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) shown++;
            });

            var counter = document.getElementById('adm-row-count');
            if (counter) counter.textContent = shown;
        });
    }

    // ── Chart.js helpers ───────────────────────────────────────────────────────
    window.AdminCharts = {

        defaults: {
            font:    'Inter, system-ui, sans-serif',
            gridCol: '#f1f5f9',
            border:  '#e2e8f0',
            muted:   '#94a3b8',
            blue:    '#3b82f6',
            green:   '#22c55e',
            amber:   '#f59e0b',
            red:     '#ef4444',
            purple:  '#a855f7',
            teal:    '#14b8a6',
        },

        applyGlobals: function () {
            if (typeof Chart === 'undefined') return;
            Chart.defaults.font.family  = this.defaults.font;
            Chart.defaults.font.size    = 12;
            Chart.defaults.color        = this.defaults.muted;
            Chart.defaults.plugins.legend.labels.boxWidth = 10;
            Chart.defaults.plugins.legend.labels.padding  = 16;
        },

        line: function (canvasId, labels, datasets, options) {
            var ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return null;

            var d = this.defaults;
            return new Chart(ctx, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: datasets.length > 1 } },
                    scales: {
                        x: {
                            grid: { color: d.gridCol },
                            border: { color: d.border },
                            ticks: { maxTicksLimit: 8 }
                        },
                        y: {
                            grid: { color: d.gridCol },
                            border: { color: d.border },
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }, options || {})
            });
        },

        bar: function (canvasId, labels, datasets, options) {
            var ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return null;

            var d = this.defaults;
            return new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: datasets },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: datasets.length > 1 } },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { color: d.border }
                        },
                        y: {
                            grid: { color: d.gridCol },
                            border: { color: d.border },
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }, options || {})
            });
        },

        doughnut: function (canvasId, labels, data, colors, options) {
            var ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return null;

            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 12 }
                        }
                    }
                }, options || {})
            });
        },
    };

    // ── Init charts if seed data is present ────────────────────────────────────
    if (window.ADMIN_CHARTS && typeof Chart !== 'undefined') {
        AdminCharts.applyGlobals();
        var cd = window.ADMIN_CHARTS;
        var d  = AdminCharts.defaults;

        // 1. Registrations over time (line)
        if (cd.registrations) {
            AdminCharts.line('chart-registrations', cd.registrations.labels, [
                {
                    label: 'Registrations',
                    data:  cd.registrations.data,
                    borderColor: d.blue,
                    backgroundColor: 'rgba(59,130,246,.08)',
                    fill: true,
                    tension: .4,
                    pointRadius: 3,
                }
            ]);
        }

        // 2. Attendance per event (bar)
        if (cd.attendance) {
            AdminCharts.bar('chart-attendance', cd.attendance.labels, [
                {
                    label: 'Peak viewers',
                    data:  cd.attendance.data,
                    backgroundColor: 'rgba(34,197,94,.7)',
                    borderColor: d.green,
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]);
        }

        // 3. Registration status breakdown (doughnut)
        if (cd.status) {
            AdminCharts.doughnut(
                'chart-status',
                cd.status.labels,
                cd.status.data,
                [d.green, d.amber, d.red, d.muted]
            );
        }

        // 4. Feedback rating distribution (bar)
        if (cd.feedback) {
            AdminCharts.bar('chart-feedback', cd.feedback.labels, [
                {
                    label: 'Responses',
                    data:  cd.feedback.data,
                    backgroundColor: [
                        'rgba(239,68,68,.7)',
                        'rgba(249,115,22,.7)',
                        'rgba(245,158,11,.7)',
                        'rgba(132,204,22,.7)',
                        'rgba(34,197,94,.7)',
                    ],
                    borderRadius: 4,
                }
            ], { plugins: { legend: { display: false } } });
        }
    }

})();
