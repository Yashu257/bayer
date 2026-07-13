<?php
/**
 * Admin — Q&A Moderation
 * Variables: $rows, $total, $page, $pages, $eventId, $event, $filter
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Questions</h1>
        <p class="adm-page-subtitle">
            <?= \Core\Security\Sanitizer::e($event['title'] ?? 'All events') ?>
            &mdash; <?= number_format((int)$total) ?> questions
        </p>
    </div>
    <div class="adm-page-actions">
        <span id="live-pending" class="adm-badge adm-badge--pending">
            <?= number_format((int)($pendingCount ?? 0)) ?> pending
        </span>
    </div>
</div>

<!-- Filter tabs -->
<ul class="nav nav-tabs adm-tab-nav mb-3">
    <?php foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'answered' => 'Answered', 'dismissed' => 'Dismissed'] as $val => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?= ($filter ?? 'all') === $val ? 'active' : '' ?>"
           href="?filter=<?= $val ?>">
            <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="adm-card">
    <div class="adm-card-body p-0">
        <div class="table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Submitted by</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            No questions found for this filter.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rows as $q): ?>
                    <tr>
                        <td>
                            <div style="max-width:420px;">
                                <?= \Core\Security\Sanitizer::e($q['question_text']) ?>
                            </div>
                            <?php if (!empty($q['answer_text'])): ?>
                            <div class="mt-1 p-2 rounded" style="background:rgba(59,130,246,.08);font-size:.8rem;">
                                <strong class="text-info">Answer:</strong>
                                <?= \Core\Security\Sanitizer::e($q['answer_text']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= \Core\Security\Sanitizer::e($q['first_name'] . ' ' . $q['last_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem;"><?= \Core\Security\Sanitizer::e($q['email'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="adm-badge adm-badge--<?= \Core\Security\Sanitizer::e($q['status'] ?? 'pending') ?>">
                                <?= ucfirst(\Core\Security\Sanitizer::e($q['status'] ?? 'pending')) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.8125rem;">
                            <?= date('d M H:i', strtotime($q['created_at'])) ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if ($q['status'] === 'pending'): ?>
                                <form method="POST"
                                      action="/admin/events/<?= (int)$eventId ?>/questions/<?= (int)$q['id'] ?>/approve">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <button class="adm-action-btn text-success" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST"
                                      action="/admin/events/<?= (int)$eventId ?>/questions/<?= (int)$q['id'] ?>/dismiss">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <button class="adm-action-btn text-warning" title="Dismiss">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if (in_array($q['status'], ['approved', 'answered'], true)): ?>
                                <!-- Answer inline -->
                                <button class="adm-action-btn text-info" title="Answer"
                                        data-bs-toggle="modal" data-bs-target="#modal-answer"
                                        data-qid="<?= (int)$q['id'] ?>"
                                        data-qtext="<?= \Core\Security\Sanitizer::e($q['question_text']) ?>"
                                        data-ans="<?= \Core\Security\Sanitizer::e($q['answer_text'] ?? '') ?>">
                                    <i class="bi bi-reply"></i>
                                </button>
                                <?php endif; ?>
                                <form method="POST"
                                      action="/admin/events/<?= (int)$eventId ?>/questions/<?= (int)$q['id'] ?>/destroy">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <button class="adm-action-btn text-danger" title="Delete"
                                            data-confirm="Delete this question?">
                                        <i class="bi bi-trash"></i>
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

    <?php if (($pages ?? 1) > 1): ?>
    <div class="adm-card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:.8125rem;">Page <?= (int)$page ?> of <?= (int)$pages ?></span>
        <div class="d-flex gap-1">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&filter=<?= urlencode($filter ?? 'all') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="?page=<?= $page + 1 ?>&filter=<?= urlencode($filter ?? 'all') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Answer Modal -->
<div class="modal fade" id="modal-answer" tabindex="-1" aria-labelledby="modal-answer-label">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--adm-card-bg);border:1px solid var(--adm-border);">
            <div class="modal-header" style="border-color:var(--adm-border);">
                <h5 class="modal-title" id="modal-answer-label" style="color:var(--adm-text);">Answer Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="answer-form" action="">
                <input type="hidden" name="_csrf_token"
                       value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                <div class="modal-body">
                    <p id="answer-question-text" class="mb-3" style="color:var(--adm-text-muted);"></p>
                    <label class="adm-label" for="answer-text">Answer</label>
                    <textarea id="answer-text" name="answer_text" class="form-control adm-input" rows="4"
                              placeholder="Type your answer…" required></textarea>
                </div>
                <div class="modal-footer" style="border-color:var(--adm-border);">
                    <button type="button" class="adm-btn adm-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="adm-btn adm-btn-primary">Save Answer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
    const modal = document.getElementById('modal-answer');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function(e){
        const btn = e.relatedTarget;
        const qid  = btn.dataset.qid;
        const base = '/admin/events/<?= (int)$eventId ?>/questions/' + qid + '/answer';
        document.getElementById('answer-form').action = base;
        document.getElementById('answer-question-text').textContent = btn.dataset.qtext;
        document.getElementById('answer-text').value = btn.dataset.ans || '';
    });
}());
</script>
