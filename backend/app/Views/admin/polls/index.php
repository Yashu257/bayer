<?php
/**
 * Admin — Polls list
 * Variables: $rows, $eventId, $pageTitle, $activePage
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Polls</h1>
        <p class="adm-page-subtitle"><?= count($rows) ?> poll(s) for this event</p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/events/<?= (int)$eventId ?>/polls/create"
           class="adm-btn adm-btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Poll
        </a>
    </div>
</div>

<?php if (empty($rows)): ?>
<div class="adm-card">
    <div class="adm-card-body text-center py-5 text-muted">
        <i class="bi bi-bar-chart display-4 d-block mb-3"></i>
        No polls created for this event yet.
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($rows as $p): ?>
    <div class="col-12 col-md-6">
        <div class="adm-card h-100">
            <div class="adm-card-header">
                <h2 class="adm-card-title"><?= \Core\Security\Sanitizer::e($p['question']) ?></h2>
                <span class="adm-badge adm-badge--<?= $p['status'] === 'live' ? 'live' : ($p['status'] === 'closed' ? 'approved' : 'pending') ?>">
                    <?= ucfirst(\Core\Security\Sanitizer::e($p['status'] ?? 'draft')) ?>
                </span>
            </div>
            <div class="adm-card-body">
                <?php $opts = json_decode($p['options'] ?? '[]', true); ?>
                <ul class="list-unstyled mb-3">
                    <?php foreach ((array)$opts as $opt): ?>
                    <li class="py-1" style="border-bottom:1px solid var(--adm-border);font-size:.875rem;color:var(--adm-text-muted);">
                        <i class="bi bi-dot"></i> <?= \Core\Security\Sanitizer::e($opt) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div style="font-size:.8rem;color:var(--adm-text-muted);">
                    <?= (int)($p['total_responses'] ?? 0) ?> responses
                </div>
            </div>
            <div class="adm-card-footer d-flex gap-2">
                <?php if ($p['status'] === 'draft'): ?>
                <form method="POST" action="/admin/events/<?= (int)$eventId ?>/polls/<?= (int)$p['id'] ?>/launch">
                    <input type="hidden" name="_csrf_token"
                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                    <button class="adm-btn adm-btn-primary adm-btn-sm">
                        <i class="bi bi-broadcast me-1"></i> Launch
                    </button>
                </form>
                <?php elseif ($p['status'] === 'live'): ?>
                <form method="POST" action="/admin/events/<?= (int)$eventId ?>/polls/<?= (int)$p['id'] ?>/close">
                    <input type="hidden" name="_csrf_token"
                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                    <button class="adm-btn adm-btn-outline adm-btn-sm">
                        <i class="bi bi-stop-circle me-1"></i> Close
                    </button>
                </form>
                <?php endif; ?>
                <a href="/admin/events/<?= (int)$eventId ?>/polls/<?= (int)$p['id'] ?>/results"
                   class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="bi bi-bar-chart me-1"></i> Results
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
