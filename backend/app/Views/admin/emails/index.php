<?php
/**
 * Admin — Email Queue Status
 * Variables: $rows, $total, $page, $pages, $counts, $statusFilter
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Email Queue</h1>
        <p class="adm-page-subtitle"><?= number_format((int)$total) ?> total emails</p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/emails/export" class="adm-btn adm-btn-outline">
            <i class="bi bi-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Status summary cards -->
<?php
$statusMap = ['pending' => ['label'=>'Pending', 'icon'=>'adm-stat-icon--amber'],
              'sending' => ['label'=>'Sending', 'icon'=>'adm-stat-icon--blue'],
              'sent'    => ['label'=>'Sent',    'icon'=>'adm-stat-icon--green'],
              'failed'  => ['label'=>'Failed',  'icon'=>'adm-stat-icon--red']];
$countMap  = [];
foreach ($counts as $c) { $countMap[$c['status']] = (int)$c['total']; }
?>
<div class="row g-3 mb-4">
    <?php foreach ($statusMap as $s => $meta): ?>
    <div class="col-6 col-md-3">
        <a href="?status=<?= $s ?>" class="text-decoration-none">
            <div class="adm-stat-card <?= ($statusFilter ?? '') === $s ? 'border-primary' : '' ?>">
                <div class="adm-stat-icon <?= $meta['icon'] ?>">
                    <i class="bi bi-envelope<?= $s === 'sent' ? '-check' : ($s === 'failed' ? '-x' : '') ?>"></i>
                </div>
                <div class="adm-stat-body">
                    <div class="adm-stat-value"><?= number_format($countMap[$s] ?? 0) ?></div>
                    <div class="adm-stat-label"><?= $meta['label'] ?></div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="adm-card mb-3">
    <div class="adm-card-body">
        <form method="GET" action="/admin/emails" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="adm-label">Status</label>
                <select name="status" class="form-select adm-input">
                    <option value="">All</option>
                    <?php foreach (['pending','sending','sent','failed'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($statusFilter ?? '') === $opt ? 'selected' : '' ?>>
                        <?= ucfirst($opt) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="adm-btn adm-btn-primary w-100">Filter</button>
            </div>
            <?php if (!empty($statusFilter)): ?>
            <div class="col-6 col-md-2">
                <a href="/admin/emails" class="adm-btn adm-btn-outline w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table -->
<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Scheduled</th>
                        <th>Sent / Failed</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">No email records found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.8rem;">#<?= (int)$row['id'] ?></td>
                        <td>
                            <div style="font-weight:600;font-size:.875rem;color:var(--adm-text);">
                                <?= \Core\Security\Sanitizer::e($row['to_name'] ?: $row['to_email']) ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--adm-text-muted);">
                                <?= \Core\Security\Sanitizer::e($row['to_email']) ?>
                            </div>
                        </td>
                        <td style="max-width:260px;">
                            <div style="font-size:.875rem;color:var(--adm-text);
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;"
                                 title="<?= \Core\Security\Sanitizer::e($row['subject']) ?>">
                                <?= \Core\Security\Sanitizer::e($row['subject']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="adm-badge adm-badge--<?= \Core\Security\Sanitizer::e($row['status']) ?>">
                                <?= ucfirst(\Core\Security\Sanitizer::e($row['status'])) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= (int)$row['attempts'] ?>/<?= (int)$row['max_attempts'] ?>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;white-space:nowrap;">
                            <?= date('d M H:i', strtotime($row['scheduled_at'])) ?>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;white-space:nowrap;">
                            <?= $row['sent_at']   ? date('d M H:i', strtotime($row['sent_at']))   : '' ?>
                            <?= $row['failed_at'] ? date('d M H:i', strtotime($row['failed_at'])) : '' ?>
                        </td>
                        <td style="max-width:200px;">
                            <?php if (!empty($row['error_message'])): ?>
                            <span class="text-danger" style="font-size:.75rem;word-break:break-word;"
                                  title="<?= \Core\Security\Sanitizer::e($row['error_message']) ?>">
                                <?= \Core\Security\Sanitizer::e(substr($row['error_message'], 0, 60)) ?>
                                <?= strlen($row['error_message']) > 60 ? '…' : '' ?>
                            </span>
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
            <a href="?page=<?= $page-1 ?>&status=<?= urlencode($statusFilter ?? '') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page+1 ?>&status=<?= urlencode($statusFilter ?? '') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
