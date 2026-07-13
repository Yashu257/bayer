<?php
/**
 * Admin — Attendance for an event
 * Variables: $rows, $total, $page, $pages, $summary, $eventId
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Attendance</h1>
        <p class="adm-page-subtitle"><?= number_format((int)$total) ?> attendees tracked</p>
    </div>
    <div class="adm-page-actions">
        <span class="adm-badge adm-badge--live me-2" id="live-count-badge">
            <i class="bi bi-broadcast me-1"></i>
            <span id="live-count"><?= (int)($summary['live_count'] ?? 0) ?></span> live
        </span>
        <a href="/admin/events/<?= (int)$eventId ?>/attendance/export"
           class="adm-btn adm-btn-outline">
            <i class="bi bi-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="adm-stat-card">
            <div class="adm-stat-icon adm-stat-icon--blue"><i class="bi bi-people"></i></div>
            <div class="adm-stat-body">
                <div class="adm-stat-value"><?= number_format((int)($summary['total_joined'] ?? 0)) ?></div>
                <div class="adm-stat-label">Total Joined</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-stat-card">
            <div class="adm-stat-icon adm-stat-icon--red"><i class="bi bi-eye"></i></div>
            <div class="adm-stat-body">
                <div class="adm-stat-value"><?= number_format((int)($summary['live_count'] ?? 0)) ?></div>
                <div class="adm-stat-label">Currently Live</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-stat-card">
            <div class="adm-stat-icon adm-stat-icon--teal"><i class="bi bi-clock-history"></i></div>
            <div class="adm-stat-body">
                <div class="adm-stat-value">
                    <?= gmdate('H:i', (int)($summary['avg_watch_seconds'] ?? 0)) ?>
                </div>
                <div class="adm-stat-label">Avg Watch Time</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-stat-card">
            <div class="adm-stat-icon adm-stat-icon--green"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="adm-stat-body">
                <div class="adm-stat-value">
                    <?= number_format((int)($summary['peak_concurrent'] ?? 0)) ?>
                </div>
                <div class="adm-stat-label">Peak Concurrent</div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Attendee</th>
                        <th>Attendee ID</th>
                        <th>Joined At</th>
                        <th>Watch Time</th>
                        <th>Last Seen</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No attendance records.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $a): ?>
                    <?php
                    $secondsAgo = time() - strtotime($a['last_heartbeat_at'] ?? 'now');
                    $isLive = $secondsAgo < 120;
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--adm-text);">
                                <?= \Core\Security\Sanitizer::e(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--adm-text-muted);">
                                <?= \Core\Security\Sanitizer::e($a['email'] ?? '') ?>
                            </div>
                        </td>
                        <td><code class="text-info"><?= \Core\Security\Sanitizer::e($a['attendee_id'] ?? '—') ?></code></td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= date('d M H:i', strtotime($a['joined_at'] ?? 'now')) ?>
                        </td>
                        <td><?= gmdate('H:i:s', (int)($a['watch_seconds'] ?? 0)) ?></td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= date('H:i:s', strtotime($a['last_heartbeat_at'] ?? 'now')) ?>
                        </td>
                        <td>
                            <?php if ($isLive): ?>
                            <span class="adm-badge adm-badge--live">Live</span>
                            <?php else: ?>
                            <span class="adm-badge" style="background:rgba(100,116,139,.2);color:#94a3b8;">Left</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
    <div class="adm-card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:.8125rem;">Page <?= (int)$page ?> of <?= (int)$pages ?></span>
        <div class="d-flex gap-1">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Live count polling -->
<script>
(function(){
    const el = document.getElementById('live-count');
    if (!el) return;
    setInterval(function(){
        fetch('/admin/events/<?= (int)$eventId ?>/attendance/live')
            .then(function(r){ return r.json(); })
            .then(function(d){ if (d && d.live !== undefined) el.textContent = d.live; });
    }, 15000);
}());
</script>
