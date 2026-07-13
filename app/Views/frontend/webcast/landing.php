<?php
/**
 * View: Event Landing Page
 *
 * Available variables (from LandingPageService::resolveForPublic):
 *   $event     — App\Models\Event
 *   $page      — App\Models\LandingPage|null
 *   $speakers  — array of speaker rows
 *   $sponsors  — array of sponsor rows
 *   $agenda    — array of agenda item rows
 *   $countdown — int (seconds until event starts)
 *
 * Layout variables set here (picked up by main.php layout):
 *   $pageStyles  — additional CSS files
 *   $pageScripts — additional JS files
 *   $inlineScript — inline JS block
 *   $bodyClass   — body class for page-specific overrides
 */

use Core\Security\Sanitizer;

// --- Layout hints passed back to the layout via variable extraction ----------
$pageStyles  = ['/assets/css/landing.css'];
$pageScripts = ['/assets/js/countdown.js'];
$bodyClass   = 'landing-page d-flex flex-column min-vh-100';

// --- Hero image: use admin-uploaded image or fall back to placeholder --------
$heroImage = !empty($page?->hero_image_path)
    ? '/uploads/thumbnails/' . Sanitizer::e($page->hero_image_path)
    : '/assets/images/hero-placeholder.jpg';

// --- Seed the countdown timer with seconds-until-start ----------------------
$inlineScript = 'window.COUNTDOWN_SECONDS = ' . (int) $countdown . ';';

// --- Helper: show flash error if any ----------------------------------------
$flashError = \Core\Session\Session::getFlash('error');
?>

<!-- =====================================================================
     SECTION 1 — HERO (Full-viewport background + event info + CTA)
     ===================================================================== -->
<section
    class="hero-section d-flex align-items-center justify-content-center text-center text-white"
    style="background-image: url('<?= $heroImage ?>');"
    aria-label="Event hero"
>
    <!-- Dark gradient overlay so text stays readable over any background photo -->
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-content position-relative py-5">

        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 mx-auto"
             style="max-width: 600px;" role="alert">
            <?= Sanitizer::e($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Event status badge -->
        <?php if ($event->isLive()): ?>
        <div class="mb-3">
            <span class="badge badge-live px-3 py-2 fs-6">
                <span class="live-dot me-1" aria-hidden="true"></span>LIVE NOW
            </span>
        </div>
        <?php elseif ($event->hasEnded()): ?>
        <div class="mb-3">
            <span class="badge bg-secondary px-3 py-2 fs-6">Event Ended</span>
        </div>
        <?php else: ?>
        <div class="mb-3">
            <span class="badge badge-upcoming px-3 py-2 fs-6">
                <i class="bi bi-calendar-event me-1"></i>Upcoming
            </span>
        </div>
        <?php endif; ?>

        <!-- Event title -->
        <h1 class="display-4 fw-bold text-white lh-sm mb-3">
            <?= Sanitizer::e($event->title) ?>
        </h1>

        <!-- Short description / sub-headline -->
        <?php
        $headline = $page?->hero_headline ?? $event->short_description ?? '';
        if ($headline):
        ?>
        <p class="lead text-white-75 mb-4 mx-auto" style="max-width: 680px;">
            <?= Sanitizer::e($headline) ?>
        </p>
        <?php endif; ?>

        <!-- Date / time / timezone strip -->
        <div class="event-meta d-flex flex-wrap justify-content-center gap-3 mb-5 fs-6">
            <span class="meta-chip">
                <i class="bi bi-calendar3 me-2"></i><?= Sanitizer::e($event->formattedDate()) ?>
            </span>
            <span class="meta-chip">
                <i class="bi bi-clock me-2"></i><?= Sanitizer::e($event->formattedTime()) ?>
            </span>
            <?php if (!empty($event->event_type)): ?>
            <span class="meta-chip text-capitalize">
                <i class="bi bi-broadcast me-2"></i><?= Sanitizer::e(str_replace('_', '-', $event->event_type)) ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- ============================================================
             COUNTDOWN TIMER
             Rendered by /assets/js/countdown.js using window.COUNTDOWN_SECONDS
             ============================================================ -->
        <?php if (!$event->hasStarted() && !$event->hasEnded()): ?>
        <div class="countdown-wrapper mb-5" id="countdownTimer" role="timer" aria-live="polite" aria-label="Event countdown">
            <p class="text-white-50 text-uppercase small letter-spacing-1 mb-3">
                Event starts in
            </p>
            <div class="countdown-grid d-inline-flex gap-2 gap-md-3">

                <div class="countdown-unit">
                    <div class="countdown-value" id="cd-days">00</div>
                    <div class="countdown-label">Days</div>
                </div>

                <div class="countdown-separator" aria-hidden="true">:</div>

                <div class="countdown-unit">
                    <div class="countdown-value" id="cd-hours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>

                <div class="countdown-separator" aria-hidden="true">:</div>

                <div class="countdown-unit">
                    <div class="countdown-value" id="cd-minutes">00</div>
                    <div class="countdown-label">Min</div>
                </div>

                <div class="countdown-separator" aria-hidden="true">:</div>

                <div class="countdown-unit">
                    <div class="countdown-value" id="cd-seconds">00</div>
                    <div class="countdown-label">Sec</div>
                </div>

            </div>
        </div>
        <?php elseif ($event->isLive()): ?>
        <div class="mb-5">
            <p class="text-white-75 fs-5">
                <i class="bi bi-broadcast-pin me-2 text-danger"></i>
                This event is currently live. Join now.
            </p>
        </div>
        <?php endif; ?>

        <!-- ============================================================
             PRIMARY CTA BUTTONS
             ============================================================ -->
        <div class="cta-buttons d-flex flex-wrap justify-content-center gap-3">

            <?php if ($event->isLive()): ?>
            <!-- Watch now (authenticated only) — handled server-side -->
            <a href="/e/<?= Sanitizer::e($event->slug) ?>/watch"
               class="btn btn-danger btn-lg px-5 shadow"
               role="button">
                <i class="bi bi-play-circle-fill me-2"></i>Watch Live
            </a>

            <?php elseif (!$event->hasEnded() && $event->registrationOpen()): ?>
            <!-- Register -->
            <a href="/e/<?= Sanitizer::e($event->slug) ?>/register"
               class="btn btn-primary btn-lg px-5 shadow cta-register"
               role="button">
                <i class="bi bi-pencil-square me-2"></i>Register Now
            </a>
            <?php endif; ?>

            <!-- Login / My Account -->
            <?php
            $authUser = \Core\Session\Session::get('auth_user');
            if ($authUser):
            ?>
            <a href="/profile"
               class="btn btn-outline-light btn-lg px-5"
               role="button">
                <i class="bi bi-person-circle me-2"></i>My Account
            </a>
            <?php else: ?>
            <a href="/login"
               class="btn btn-outline-light btn-lg px-5 cta-login"
               role="button">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
            <?php endif; ?>

        </div><!-- /.cta-buttons -->

    </div><!-- /.hero-content -->
</section>

<!-- =====================================================================
     SECTION 2 — ABOUT (only shown if landing page has body content)
     ===================================================================== -->
<?php if (!empty($page?->body_content)): ?>
<section id="about" class="section-about py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="section-title text-center mb-4">About This Event</h2>
                <div class="section-divider mx-auto mb-5"></div>
                <div class="about-content lh-lg text-muted">
                    <?= $page->body_content /* Admin-controlled HTML — stored as sanitised content */ ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SECTION 3 — AGENDA
     ===================================================================== -->
<?php if (!empty($agenda)): ?>
<section id="agenda" class="section-agenda py-5 bg-light">
    <div class="container">

        <h2 class="section-title text-center mb-2">Agenda</h2>
        <div class="section-divider mx-auto mb-5"></div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="agenda-list">
                    <?php foreach ($agenda as $index => $item): ?>
                    <div class="agenda-item d-flex gap-3 gap-md-4 mb-4">

                        <!-- Step number -->
                        <div class="agenda-number flex-shrink-0">
                            <?= $index + 1 ?>
                        </div>

                        <div class="flex-grow-1">
                            <!-- Time -->
                            <?php if (!empty($item['starts_at'])): ?>
                            <div class="agenda-time text-primary small fw-semibold mb-1">
                                <i class="bi bi-clock me-1"></i>
                                <?= Sanitizer::e(date('g:i A', strtotime($item['starts_at']))) ?>
                                <?php if (!empty($item['ends_at'])): ?>
                                &ndash; <?= Sanitizer::e(date('g:i A', strtotime($item['ends_at']))) ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <h5 class="agenda-title fw-semibold mb-1">
                                <?= Sanitizer::e($item['title']) ?>
                            </h5>

                            <?php if (!empty($item['description'])): ?>
                            <p class="text-muted small mb-1">
                                <?= Sanitizer::e($item['description']) ?>
                            </p>
                            <?php endif; ?>

                            <!-- Speaker name (joined from query) -->
                            <?php if (!empty($item['first_name'])): ?>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <?php if (!empty($item['photo_path'])): ?>
                                <img src="/uploads/speakers/<?= Sanitizer::e($item['photo_path']) ?>"
                                     alt="<?= Sanitizer::e($item['first_name']) ?>"
                                     class="rounded-circle"
                                     width="28" height="28"
                                     loading="lazy">
                                <?php else: ?>
                                <div class="agenda-speaker-avatar rounded-circle">
                                    <?= Sanitizer::e(strtoupper($item['first_name'][0] ?? '?')) ?>
                                </div>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <?= Sanitizer::e($item['first_name'] . ' ' . $item['last_name']) ?>
                                    <?php if (!empty($item['job_title'])): ?>
                                    &middot; <?= Sanitizer::e($item['job_title']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SECTION 4 — SPEAKERS
     ===================================================================== -->
<?php if (!empty($speakers)): ?>
<section id="speakers" class="section-speakers py-5 bg-white">
    <div class="container">

        <h2 class="section-title text-center mb-2">Speakers</h2>
        <div class="section-divider mx-auto mb-5"></div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($speakers as $speaker): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="speaker-card text-center h-100">

                    <!-- Photo / avatar -->
                    <div class="speaker-photo-wrap mx-auto mb-3">
                        <?php if (!empty($speaker['photo_path'])): ?>
                        <img
                            src="/uploads/speakers/<?= Sanitizer::e($speaker['photo_path']) ?>"
                            alt="<?= Sanitizer::e($speaker['first_name'] . ' ' . $speaker['last_name']) ?>"
                            class="speaker-photo rounded-circle"
                            width="96" height="96"
                            loading="lazy"
                        >
                        <?php else: ?>
                        <div class="speaker-photo-fallback rounded-circle d-flex align-items-center justify-content-center mx-auto">
                            <span class="fs-2 fw-bold">
                                <?= Sanitizer::e(strtoupper(($speaker['first_name'][0] ?? '') . ($speaker['last_name'][0] ?? ''))) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <h6 class="fw-semibold mb-0">
                        <?= Sanitizer::e($speaker['first_name'] . ' ' . $speaker['last_name']) ?>
                    </h6>

                    <?php if (!empty($speaker['event_role'])): ?>
                    <span class="badge bg-primary-subtle text-primary small mt-1">
                        <?= Sanitizer::e($speaker['event_role']) ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($speaker['job_title'])): ?>
                    <p class="text-muted small mt-1 mb-1">
                        <?= Sanitizer::e($speaker['job_title']) ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($speaker['company'])): ?>
                    <p class="text-muted small mb-2">
                        <?= Sanitizer::e($speaker['company']) ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($speaker['linkedin_url'])): ?>
                    <a href="<?= Sanitizer::e($speaker['linkedin_url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-sm btn-outline-secondary"
                       aria-label="<?= Sanitizer::e($speaker['first_name']) ?> on LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SECTION 5 — BOTTOM CTA STRIP
     ===================================================================== -->
<?php if (!$event->hasEnded()): ?>
<section class="section-bottom-cta py-5 bg-primary text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <?php if ($event->isLive()): ?>
                <h3 class="fw-bold mb-3">This event is live right now.</h3>
                <a href="/e/<?= Sanitizer::e($event->slug) ?>/watch"
                   class="btn btn-danger btn-lg px-5">
                    <i class="bi bi-play-circle-fill me-2"></i>Join the Webcast
                </a>

                <?php elseif ($event->registrationOpen()): ?>
                <h3 class="fw-bold mb-2">Don't miss this event.</h3>
                <p class="mb-4 text-white-75">Secure your spot — registration is free.</p>
                <a href="/e/<?= Sanitizer::e($event->slug) ?>/register"
                   class="btn btn-light btn-lg px-5 text-primary fw-semibold">
                    <i class="bi bi-pencil-square me-2"></i>Register Now
                </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SECTION 6 — SPONSORS (only if visible flag is on)
     ===================================================================== -->
<?php if (!empty($sponsors)): ?>
<section class="section-sponsors py-4 bg-light">
    <div class="container">
        <p class="text-center text-muted small text-uppercase letter-spacing-1 mb-4">
            Supported By
        </p>
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4">
            <?php foreach ($sponsors as $sponsor): ?>
            <?php
                $sponsorInner = !empty($sponsor['logo_path'])
                    ? '<img src="/uploads/documents/' . Sanitizer::e($sponsor['logo_path']) . '" alt="' . Sanitizer::e($sponsor['name']) . '" height="36" class="sponsor-logo" loading="lazy">'
                    : '<span class="text-muted fw-semibold small">' . Sanitizer::e($sponsor['name']) . '</span>';
            ?>
            <?php if (!empty($sponsor['website_url'])): ?>
            <a href="<?= Sanitizer::e($sponsor['website_url']) ?>"
               target="_blank" rel="noopener noreferrer"
               class="sponsor-link" title="<?= Sanitizer::e($sponsor['name']) ?>">
                <?= $sponsorInner ?>
            </a>
            <?php else: ?>
            <div class="sponsor-link"><?= $sponsorInner ?></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
