<?php
/**
 * Admin — Attendees list
 * Variables: $rows, $total, $page, $pages, $search, $status
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Attendees</h1>
        <p class="adm-page-subtitle"><?= number_format((int)$total) ?> total users</p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/users/export" class="adm-btn adm-btn-outline">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="adm-card mb-3">
    <div class="adm-card-body">
        <form method="GET" action="/admin/users" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="adm-label" for="s-search">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="s-search" name="search" value="<?= \Core\Security\Sanitizer::e($search ?? '') ?>"
                           class="form-control adm-input" placeholder="Name, email, attendee ID…">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="adm-label" for="s-status">Status</label>
                <select id="s-status" name="status" class="form-select adm-input">
                    <option value="">All statuses</option>
                    <?php foreach (['approved', 'pending', 'rejected', 'banned'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($status ?? '') === $opt ? 'selected' : '' ?>>
                        <?= ucfirst($opt) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="adm-btn adm-btn-primary w-100">Filter</button>
            </div>
            <?php if (!empty($search) || !empty($status)): ?>
            <div class="col-12 col-md-2">
                <a href="/admin/users" class="adm-btn adm-btn-outline w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table -->
<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table" id="attendees-table">
                <thead>
                    <tr>
                        <th>Attendee</th>
                        <th>Email</th>
                        <th>Specialty</th>
                        <th>NPI</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No attendees found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--adm-text);">
                                <?= \Core\Security\Sanitizer::e($u['first_name'] . ' ' . $u['last_name']) ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--adm-text-muted);">
                                <?= \Core\Security\Sanitizer::e($u['attendee_id'] ?? '') ?>
                            </div>
                        </td>
                        <td><?= \Core\Security\Sanitizer::e($u['email']) ?></td>
                        <td><?= \Core\Security\Sanitizer::e($u['specialty'] ?? '—') ?></td>
                        <td><?= \Core\Security\Sanitizer::e($u['npi_number'] ?? '—') ?></td>
                        <td>
                            <span class="adm-badge adm-badge--<?= \Core\Security\Sanitizer::e($u['approval_status'] ?? 'pending') ?>">
                                <?= ucfirst(\Core\Security\Sanitizer::e($u['approval_status'] ?? 'pending')) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/admin/users/<?= (int)$u['id'] ?>"
                                   class="adm-action-btn" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (($u['approval_status'] ?? '') !== 'approved'): ?>
                                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/status">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="adm-action-btn text-success" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="/admin/users/<?= (int)$u['id'] ?>/status">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <input type="hidden" name="status" value="banned">
                                    <button type="submit" class="adm-action-btn text-danger" title="Ban"
                                            data-confirm="Ban this user?">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (($pages ?? 1) > 1): ?>
    <div class="adm-card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:.8125rem;">
            Page <?= (int)$page ?> of <?= (int)$pages ?>
        </span>
        <div class="d-flex gap-1">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>"
               class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="bi bi-chevron-left"></i>
            </a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>"
               class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="bi bi-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
