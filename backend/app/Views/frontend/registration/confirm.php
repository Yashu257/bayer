<?php
/**
 * Registration confirmed — success page.
 *
 * Variables:
 *   @var \App\Models\Event $event
 *   @var string            $attendeeId   e.g. PW-2025-000042
 *   @var string            $name
 *   @var string            $email
 *   @var string            $pageTitle
 */

use Core\Security\Sanitizer;

$bodyClass = 'registration-page';

ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 text-center">

            <!-- Success icon -->
            <div class="confirm-icon mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
            </div>

            <h1 class="h3 fw-bold mb-2">You're registered!</h1>
            <p class="text-muted mb-4">
                A confirmation has been sent to
                <strong><?= Sanitizer::e($email) ?></strong>.
            </p>

            <!-- Attendee ID card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-4">
                    <p class="text-muted small mb-1 text-uppercase fw-medium tracking-wider">
                        Your Attendee ID
                    </p>
                    <p class="attendee-id display-6 fw-bold text-primary mb-0">
                        <?= Sanitizer::e($attendeeId) ?>
                    </p>
                    <p class="text-muted small mt-2 mb-0">
                        Keep this ID handy — you will need it to access the event.
                    </p>
                </div>
            </div>

            <!-- Event summary -->
            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <p class="fw-semibold mb-1"><?= Sanitizer::e($event->title) ?></p>
                    <p class="text-muted small mb-0">
                        <?= Sanitizer::e($event->formatted_date ?? '') ?>
                        <?php if (!empty($event->formatted_time)): ?>
                            &nbsp;·&nbsp; <?= Sanitizer::e($event->formatted_time) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="/event/<?= Sanitizer::e($event->slug) ?>"
                   class="btn btn-primary px-4">
                    <i class="bi bi-play-circle me-1"></i>View Event
                </a>
                <a href="/" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-house me-1"></i>Home
                </a>
            </div>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/frontend/layouts/main.php';
