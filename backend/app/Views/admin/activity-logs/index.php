<?php
/**
 * Admin — Activity Logs
 * Variables: $rows, $total, $page, $pages, $search, $action, $actions (distinct list)
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Activity Logs</h1>
        <p class="adm-page-subtitle"><?= number_format((int)$total) ?> log entries</p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/activity-logs/export" class="adm-btn adm-btn-outline">
            <i class="bi bi-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Filters -->
<div class="adm-card mb-3">
    <div class="adm-card-body">
        <form method="GET" action="/admin/activity-logs" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="adm-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" value="<?= \Core\Security\Sanitizer::e($search ?? '') ?>"
                           class="form-control adm-input" placeholder="User, IP, message…">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="adm-label">Action</label>
                <select name="action" class="form-select adm-input">
                    <option value="">All actions</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= \Core\Security\Sanitizer::e($a) ?>"
                            <?= ($action ?? '') === $a ? 'selected' : '' ?>>
                        <?= \Core\Security\Sanitizer::e($a) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="adm-btn adm-btn-primary w-100">Filter</button>
            </div>
            <?php if (!empty($search) || !empty($action)): ?>
            <div class="col-12 col-md-2">
                <a href="/admin/activity-logs" class="adm-btn adm-btn-outline w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>Actor</th>
                        <th>Message / Payload</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No log entries found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $log): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.8125rem;white-space:nowrap;">
                            <?= date('d M Y H:i:s', strtotime($log['created_at'])) ?>
                        </td>
                        <td>
                            <span class="adm-badge" style="background:rgba(99,102,241,.15);color:#a5b4fc;font-size:.75rem;">
                                <?= \Core\Security\Sanitizer::e($log['action'] ?? '—') ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-size:.875rem;color:var(--adm-text);">
                                <?= \Core\Security\Sanitizer::e($log['actor_email'] ?? ($log['actor_id'] ? '#' . $log['actor_id'] : 'System')) ?>
                            </div>
                        </td>
                        <td>
                            <div style="max-width:380px;font-size:.8125rem;color:var(--adm-text-muted);
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                 title="<?= \Core\Security\Sanitizer::e($log['message'] ?? '') ?>">
                                <?= \Core\Security\Sanitizer::e($log['message'] ?? '—') ?>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= \Core\Security\Sanitizer::e($log['ip_address'] ?? '—') ?>
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
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&action=<?= urlencode($action ?? '') ?>"
               class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&action=<?= urlencode($action ?? '') ?>"
               class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
