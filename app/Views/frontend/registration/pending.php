<?php
/**
 * Registration pending approval page.
 *
 * Variables:
 *   @var \App\Models\Event $event
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

            <!-- Pending icon -->
            <div class="mb-4">
                <i class="bi bi-hourglass-split text-warning" style="font-size:4rem;"></i>
            </div>

            <h1 class="h3 fw-bold mb-2">Registration received!</h1>
            <p class="text-muted mb-4">
                Hi <strong><?= Sanitizer::e($name) ?></strong>, thank you for registering for
                <strong><?= Sanitizer::e($event->title) ?></strong>.
            </p>

            <!-- Pending notice card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-4">
                    <div class="d-flex align-items-start gap-3 text-start">
                        <i class="bi bi-info-circle-fill text-warning mt-1 flex-shrink-0" style="font-size:1.4rem;"></i>
                        <div>
                            <p class="fw-semibold mb-1">Approval required</p>
                            <p class="text-muted small mb-0">
                                This event requires organiser approval.
                                Your registration is under review and you will receive a confirmation email
                                at <strong><?= Sanitizer::e($email) ?></strong> once approved.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What to expect -->
            <div class="card border-0 bg-light mb-4">
                <div class="card-body text-start">
                    <p class="fw-semibold mb-2 small text-uppercase text-muted">What happens next</p>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-envelope-check text-primary me-2"></i>
                            You'll receive an approval notification at <strong><?= Sanitizer::e($email) ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-ticket-perforated text-primary me-2"></i>
                            Your attendee ID will be included in the approval email
                        </li>
                        <li>
                            <i class="bi bi-play-circle text-primary me-2"></i>
                            Use the event link in the email to join on event day
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="/event/<?= Sanitizer::e($event->slug) ?>"
                   class="btn btn-outline-primary px-4">
                    <i class="bi bi-arrow-left me-1"></i>Back to Event
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
