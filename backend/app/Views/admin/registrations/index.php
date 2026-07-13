<?php
/**
 * Admin — Registration Reports
 * Variables: $events (summary by event), $pageTitle, $activePage
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Registration Reports</h1>
        <p class="adm-page-subtitle">Registrations grouped by event</p>
    </div>
</div>

<?php if (empty($events)): ?>
<div class="adm-card">
    <div class="adm-card-body text-center py-5 text-muted">
        <i class="bi bi-inbox display-4 d-block mb-3"></i>
        No events with registrations yet.
    </div>
</div>
<?php else: ?>
<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Approved</th>
                        <th>Pending</th>
                        <th>Rejected</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--adm-text);">
                                <?= \Core\Security\Sanitizer::e($e['event_title'] ?? '—') ?>
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= \Core\Security\Sanitizer::e($e['event_date'] ?? '—') ?>
                        </td>
                        <td><strong><?= (int)$e['total'] ?></strong></td>
                        <td>
                            <span class="adm-badge adm-badge--approved"><?= (int)$e['approved'] ?></span>
                        </td>
                        <td>
                            <span class="adm-badge adm-badge--pending"><?= (int)$e['pending'] ?></span>
                        </td>
                        <td>
                            <span class="adm-badge adm-badge--rejected"><?= (int)$e['rejected'] ?></span>
                        </td>
                        <td>
                            <a href="/admin/events/<?= (int)$e['event_id'] ?>/registrations"
                               class="adm-btn adm-btn-outline adm-btn-sm">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
