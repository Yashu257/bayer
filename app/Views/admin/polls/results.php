<?php
/**
 * Admin — Poll Results
 * Variables: $poll, $options (with vote_count), $total, $eventId
 */
$maxVotes = max(array_column($options ?? [], 'vote_count') ?: [0]);
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Poll Results</h1>
        <p class="adm-page-subtitle"><?= \Core\Security\Sanitizer::e($poll['question'] ?? '') ?></p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/events/<?= (int)$eventId ?>/polls" class="adm-btn adm-btn-outline">
            <i class="bi bi-arrow-left me-1"></i> Back to Polls
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Bar results -->
    <div class="col-12 col-xl-8">
        <div class="adm-card">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Vote Distribution</h2>
                <span class="text-muted" style="font-size:.8125rem;"><?= (int)$total ?> total responses</span>
            </div>
            <div class="adm-card-body">
                <?php if (empty($options)): ?>
                <p class="text-muted text-center py-4">No responses yet.</p>
                <?php else: ?>
                <?php foreach ($options as $opt): ?>
                <?php $pct = $total > 0 ? round(($opt['vote_count'] / $total) * 100) : 0; ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.875rem;color:var(--adm-text);">
                            <?= \Core\Security\Sanitizer::e($opt['option_text']) ?>
                        </span>
                        <span style="font-size:.8125rem;color:var(--adm-text-muted);">
                            <?= (int)$opt['vote_count'] ?> (<?= $pct ?>%)
                        </span>
                    </div>
                    <div class="progress" style="height:10px;background:var(--adm-border);">
                        <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:var(--adm-accent);"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Poll meta -->
    <div class="col-12 col-xl-4">
        <div class="adm-card">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Poll Info</h2>
            </div>
            <div class="adm-card-body">
                <dl class="row mb-0" style="font-size:.875rem;">
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <span class="adm-badge adm-badge--<?= $poll['status'] === 'live' ? 'live' : 'approved' ?>">
                            <?= ucfirst(\Core\Security\Sanitizer::e($poll['status'] ?? 'closed')) ?>
                        </span>
                    </dd>
                    <dt class="col-5 text-muted">Total votes</dt>
                    <dd class="col-7"><?= (int)$total ?></dd>
                    <dt class="col-5 text-muted">Options</dt>
                    <dd class="col-7"><?= count($options) ?></dd>
                    <dt class="col-5 text-muted">Created</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($poll['created_at'] ?? 'now')) ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
