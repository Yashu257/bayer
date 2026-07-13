<?php
/**
 * Admin — Per-event Settings
 * Variables: $grouped, $eventId, $pageTitle
 */
?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Event Settings</h1>
        <p class="adm-page-subtitle">Overrides applied to event #<?= (int)$eventId ?></p>
    </div>
    <div class="adm-page-actions">
        <a href="/admin/settings" class="adm-btn adm-btn-outline">
            <i class="bi bi-sliders me-1"></i> Platform Settings
        </a>
    </div>
</div>

<form method="POST" action="/admin/events/<?= (int)$eventId ?>/settings">
    <input type="hidden" name="_csrf_token"
           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">

    <?php foreach ($grouped as $group => $settings): ?>
    <div class="adm-card mb-3">
        <div class="adm-card-header">
            <h2 class="adm-card-title"><?= \Core\Security\Sanitizer::e($group) ?></h2>
        </div>
        <div class="adm-card-body">
            <div class="row g-3">
                <?php foreach ($settings as $s): ?>
                <div class="col-12 col-md-6">
                    <label class="adm-label" for="es_<?= \Core\Security\Sanitizer::e($s['key']) ?>">
                        <?= \Core\Security\Sanitizer::e($s['label'] ?? $s['key']) ?>
                    </label>
                    <?php if (($s['type'] ?? 'text') === 'boolean'): ?>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="settings[<?= \Core\Security\Sanitizer::e($s['key']) ?>]" value="0">
                        <input class="form-check-input" type="checkbox"
                               id="es_<?= \Core\Security\Sanitizer::e($s['key']) ?>"
                               name="settings[<?= \Core\Security\Sanitizer::e($s['key']) ?>]"
                               value="1" <?= ($s['value'] ?? '0') == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label text-muted"
                               for="es_<?= \Core\Security\Sanitizer::e($s['key']) ?>">Enabled</label>
                    </div>
                    <?php elseif (($s['type'] ?? 'text') === 'textarea'): ?>
                    <textarea id="es_<?= \Core\Security\Sanitizer::e($s['key']) ?>"
                              name="settings[<?= \Core\Security\Sanitizer::e($s['key']) ?>]"
                              class="form-control adm-input" rows="3"><?= \Core\Security\Sanitizer::e($s['value'] ?? '') ?></textarea>
                    <?php else: ?>
                    <input type="text"
                           id="es_<?= \Core\Security\Sanitizer::e($s['key']) ?>"
                           name="settings[<?= \Core\Security\Sanitizer::e($s['key']) ?>]"
                           value="<?= \Core\Security\Sanitizer::e($s['value'] ?? '') ?>"
                           class="form-control adm-input">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="adm-btn adm-btn-primary">
            <i class="bi bi-floppy me-1"></i> Save Event Settings
        </button>
    </div>
</form>
