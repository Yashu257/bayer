<?php
/**
 * Registration form view.
 *
 * Variables injected by RegistrationController::showForm():
 *   @var \App\Models\Event $event
 *   @var array             $errors     ['field' => ['msg1', ...]]
 *   @var array             $old        Previous input to repopulate on error
 *   @var string            $csrfToken
 *   @var string            $pageTitle
 */

use Core\Security\Sanitizer;

$pageStyles   = ['/assets/css/registration.css'];
$pageScripts  = [];
$bodyClass    = 'registration-page';

/**
 * Helper: get first error message for a field.
 */
$err = static fn(string $field): string =>
    isset($errors[$field][0]) ? '<div class="invalid-feedback d-block">' . Sanitizer::e($errors[$field][0]) . '</div>' : '';

/**
 * Helper: return "is-invalid" when the field has an error.
 */
$cls = static fn(string $field): string =>
    isset($errors[$field]) ? 'is-invalid' : '';

/**
 * Helper: repopulate a text field safely.
 */
$val = static fn(string $field): string =>
    Sanitizer::e($old[$field] ?? '');

ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">

            <!-- Event header -->
            <div class="text-center mb-4">
                <p class="text-muted mb-1 small text-uppercase tracking-wider">
                    <?= Sanitizer::e($event->formatted_date ?? '') ?>
                </p>
                <h1 class="h3 fw-bold mb-2"><?= Sanitizer::e($event->title) ?></h1>
                <p class="text-muted">Fill in your details below to register for this event.</p>
            </div>

            <!-- Form errors banner -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Please correct the errors below and try again.
                </div>
            <?php endif; ?>

            <!-- Registration card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">

                    <form method="POST"
                          action="/event/<?= Sanitizer::e($event->slug) ?>/register"
                          novalidate
                          autocomplete="on">

                        <input type="hidden" name="_csrf_token" value="<?= Sanitizer::e($csrfToken) ?>">

                        <!-- ── Name row ── -->
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="first_name" class="form-label fw-medium">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       class="form-control <?= $cls('first_name') ?>"
                                       value="<?= $val('first_name') ?>"
                                       maxlength="80"
                                       required
                                       autocomplete="given-name">
                                <?= $err('first_name') ?>
                            </div>

                            <div class="col-sm-6">
                                <label for="last_name" class="form-label fw-medium">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       class="form-control <?= $cls('last_name') ?>"
                                       value="<?= $val('last_name') ?>"
                                       maxlength="80"
                                       required
                                       autocomplete="family-name">
                                <?= $err('last_name') ?>
                            </div>
                        </div>

                        <!-- ── Email ── -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control <?= $cls('email') ?>"
                                   value="<?= $val('email') ?>"
                                   maxlength="255"
                                   required
                                   autocomplete="email">
                            <?= $err('email') ?>
                        </div>

                        <!-- ── Mobile ── -->
                        <div class="mb-3">
                            <label for="mobile" class="form-label fw-medium">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel"
                                   id="mobile"
                                   name="mobile"
                                   class="form-control <?= $cls('mobile') ?>"
                                   value="<?= $val('mobile') ?>"
                                   maxlength="20"
                                   placeholder="+91 98765 43210"
                                   required
                                   autocomplete="tel">
                            <?= $err('mobile') ?>
                        </div>

                        <!-- ── Company ── -->
                        <div class="mb-3">
                            <label for="company" class="form-label fw-medium">
                                Company / Organisation <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="company"
                                   name="company"
                                   class="form-control <?= $cls('company') ?>"
                                   value="<?= $val('company') ?>"
                                   maxlength="150"
                                   required
                                   autocomplete="organization">
                            <?= $err('company') ?>
                        </div>

                        <!-- ── Designation ── -->
                        <div class="mb-3">
                            <label for="designation" class="form-label fw-medium">
                                Designation / Job Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="designation"
                                   name="designation"
                                   class="form-control <?= $cls('designation') ?>"
                                   value="<?= $val('designation') ?>"
                                   maxlength="100"
                                   required
                                   autocomplete="organization-title">
                            <?= $err('designation') ?>
                        </div>

                        <!-- ── Location row ── -->
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <label for="city" class="form-label fw-medium">
                                    City <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="city"
                                       name="city"
                                       class="form-control <?= $cls('city') ?>"
                                       value="<?= $val('city') ?>"
                                       maxlength="100"
                                       required
                                       autocomplete="address-level2">
                                <?= $err('city') ?>
                            </div>

                            <div class="col-sm-4">
                                <label for="state" class="form-label fw-medium">
                                    State / Province <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="state"
                                       name="state"
                                       class="form-control <?= $cls('state') ?>"
                                       value="<?= $val('state') ?>"
                                       maxlength="100"
                                       required
                                       autocomplete="address-level1">
                                <?= $err('state') ?>
                            </div>

                            <div class="col-sm-4">
                                <label for="country" class="form-label fw-medium">
                                    Country <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="country"
                                       name="country"
                                       class="form-control <?= $cls('country') ?>"
                                       value="<?= $val('country') ?>"
                                       maxlength="100"
                                       required
                                       autocomplete="country-name">
                                <?= $err('country') ?>
                            </div>
                        </div>

                        <!-- ── Consent ── -->
                        <div class="mb-4">
                            <div class="form-check <?= isset($errors['consent']) ? 'is-invalid' : '' ?>">
                                <input type="checkbox"
                                       id="consent"
                                       name="consent"
                                       value="1"
                                       class="form-check-input <?= $cls('consent') ?>"
                                       <?= !empty($old['consent']) ? 'checked' : '' ?>
                                       required>
                                <label for="consent" class="form-check-label small">
                                    I agree to receive event communications and accept the
                                    <a href="/privacy-policy" target="_blank" rel="noopener">Privacy Policy</a>
                                    and
                                    <a href="/terms" target="_blank" rel="noopener">Terms of Use</a>.
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                            <?= $err('consent') ?>
                        </div>

                        <!-- ── Submit ── -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                <i class="bi bi-check2-circle me-2"></i>Register Now
                            </button>
                        </div>

                        <p class="text-center text-muted small mt-3 mb-0">
                            Already registered?
                            <a href="/login">Sign in</a> to access the event.
                        </p>

                    </form>

                </div><!-- /card-body -->
            </div><!-- /card -->

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/frontend/layouts/main.php';
