<?php
/**
 * Webcast room view.
 *
 * Variables injected by WebcastController::room():
 *   @var \App\Models\Event        $event
 *   @var \App\Models\Registration $registration
 *   @var array                    $streamConfig   Provider embed config
 *   @var array                    $webcastData    Full JSON payload
 *   @var string                   $inlineScript   window.WEBCAST = {...}
 *   @var string                   $pageTitle
 *   @var string                   $bodyClass      'webcast-page'
 *   @var array                    $pageStyles
 *   @var array                    $pageScripts
 *
 * Layout note:
 *   webcast-page has its own full-height layout — the global header/footer
 *   are hidden via .webcast-page body class in webcast.css.
 *   The room renders inside a dedicated shell, not the standard main.php layout.
 */

use Core\Security\Sanitizer;
use Core\Security\CsrfGuard;
use Core\Session\Session;

$csrfToken = CsrfGuard::token();
$slug      = Sanitizer::e($event->slug);
$eventName = Sanitizer::e($event->title);
$attendeeName = Sanitizer::e($registration->full_name ?? '');
$attendeeId   = Sanitizer::e($registration->attendee_id ?? '');
?>
<!DOCTYPE html>
<html lang="en" class="webcast-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= Sanitizer::e($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Bootstrap 5 -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Webcast styles -->
    <link rel="stylesheet" href="/assets/css/webcast.css">
</head>
<body class="webcast-body">

<!-- ═══════════════════════════════════════════════════════════════════════════
     TOP BAR
════════════════════════════════════════════════════════════════════════════ -->
<header class="wc-topbar d-flex align-items-center justify-content-between px-3 px-md-4">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <!-- Live indicator -->
        <span class="wc-live-badge">
            <span class="wc-live-dot"></span>LIVE
        </span>
        <!-- Event title -->
        <h1 class="wc-topbar-title text-truncate mb-0"><?= $eventName ?></h1>
    </div>

    <div class="d-flex align-items-center gap-2 flex-shrink-0">
        <!-- Attendee chip -->
        <span class="wc-attendee-chip d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-person-circle"></i>
            <span class="text-truncate" style="max-width:140px;"><?= $attendeeName ?></span>
        </span>
        <!-- Feedback -->
        <a href="/e/<?= $slug ?>/feedback"
           class="btn btn-sm btn-outline-warning wc-feedback-btn"
           target="_blank" rel="noopener">
            <i class="bi bi-star me-1"></i><span class="d-none d-sm-inline">Feedback</span>
        </a>
        <!-- Logout / leave -->
        <form method="POST" action="/logout" class="d-inline mb-0">
            <input type="hidden" name="_csrf_token" value="<?= Sanitizer::e($csrfToken) ?>">
            <button type="submit" class="btn btn-sm btn-outline-secondary wc-leave-btn">
                <i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Leave</span>
            </button>
        </form>
    </div>
</header>

<!-- ═══════════════════════════════════════════════════════════════════════════
     MAIN ROOM — video + sidebar
════════════════════════════════════════════════════════════════════════════ -->
<div class="wc-room">

    <!-- ── VIDEO PANE ──────────────────────────────────────────────────────── -->
    <main class="wc-player-pane">

        <!-- Player container — webcast.js mounts the iframe/video here -->
        <div id="stream-player" class="wc-player-wrap" aria-label="Live stream player">

            <!-- Placeholder rendered until JS replaces it (or if provider = placeholder) -->
            <div id="stream-placeholder" class="wc-placeholder d-flex flex-column align-items-center justify-content-center">
                <div class="wc-placeholder-icon mb-3">
                    <i class="bi bi-broadcast"></i>
                </div>
                <p class="fw-semibold mb-1">Live stream</p>
                <p class="text-muted small mb-0">The stream will appear here momentarily.</p>
            </div>

        </div><!-- /#stream-player -->

        <!-- Below-player strip: attendee ID + event info -->
        <div class="wc-player-footer d-flex align-items-center justify-content-between px-3 py-2">
            <span class="small text-muted">
                <i class="bi bi-ticket-perforated me-1"></i><?= $attendeeId ?>
            </span>
            <div class="d-flex gap-3">
                <!-- Mobile-only feedback button (mirrored from topbar) -->
                <a href="/e/<?= $slug ?>/feedback"
                   class="btn btn-sm btn-outline-warning d-md-none"
                   target="_blank" rel="noopener">
                    <i class="bi bi-star me-1"></i>Feedback
                </a>
            </div>
        </div>

    </main><!-- /.wc-player-pane -->

    <!-- ── SIDEBAR ─────────────────────────────────────────────────────────── -->
    <aside class="wc-sidebar" aria-label="Interaction panel">

        <!-- Tab navigation -->
        <ul class="nav nav-tabs wc-sidebar-tabs" id="sidebarTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-qa"
                        data-bs-toggle="tab" data-bs-target="#pane-qa"
                        type="button" role="tab" aria-controls="pane-qa" aria-selected="true">
                    <i class="bi bi-chat-square-text me-1"></i>Q&amp;A
                    <span class="badge bg-primary ms-1 wc-qa-count d-none">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-poll"
                        data-bs-toggle="tab" data-bs-target="#pane-poll"
                        type="button" role="tab" aria-controls="pane-poll" aria-selected="false">
                    <i class="bi bi-bar-chart me-1"></i>Poll
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-quiz"
                        data-bs-toggle="tab" data-bs-target="#pane-quiz"
                        type="button" role="tab" aria-controls="pane-quiz" aria-selected="false">
                    <i class="bi bi-patch-question me-1"></i>Quiz
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-announce"
                        data-bs-toggle="tab" data-bs-target="#pane-announce"
                        type="button" role="tab" aria-controls="pane-announce" aria-selected="false">
                    <i class="bi bi-megaphone me-1"></i>
                    <span class="d-none d-xl-inline">Announcements</span>
                    <span class="d-xl-none">Info</span>
                    <span class="badge bg-danger ms-1 wc-announce-badge d-none">!</span>
                </button>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content wc-sidebar-content" id="sidebarTabContent">

            <!-- ── Q&A Pane ─────────────────────────────────────────────── -->
            <div class="tab-pane fade show active h-100 d-flex flex-column"
                 id="pane-qa" role="tabpanel" aria-labelledby="tab-qa">

                <!-- Question list (scrollable) -->
                <div class="wc-qa-list flex-grow-1" id="qa-list">
                    <!-- Populated by webcast.js on load and polling -->
                    <div class="wc-empty-state py-5 text-center text-muted" id="qa-empty">
                        <i class="bi bi-chat-square-dots fs-2 d-block mb-2 opacity-50"></i>
                        <p class="small mb-0">No questions yet. Be the first!</p>
                    </div>
                </div>

                <!-- Submit question form -->
                <div class="wc-qa-form border-top p-3">
                    <form id="qa-form" novalidate>
                        <input type="hidden" name="_csrf_token" value="<?= Sanitizer::e($csrfToken) ?>">
                        <div class="mb-2">
                            <textarea
                                id="qa-input"
                                name="question_text"
                                class="form-control form-control-sm"
                                rows="2"
                                maxlength="400"
                                placeholder="Ask a question…"
                                aria-label="Your question"
                            ></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size:.75rem;">
                                <span id="qa-char-count">400</span> chars left
                            </span>
                            <button type="submit" class="btn btn-primary btn-sm px-3" id="qa-submit">
                                <i class="bi bi-send me-1"></i>Send
                            </button>
                        </div>
                        <div id="qa-error" class="text-danger small mt-1" style="display:none;"></div>
                    </form>
                </div>

            </div><!-- /#pane-qa -->

            <!-- ── Poll Pane ─────────────────────────────────────────────── -->
            <div class="tab-pane fade h-100 d-flex flex-column"
                 id="pane-poll" role="tabpanel" aria-labelledby="tab-poll">

                <div class="flex-grow-1 overflow-auto p-3" id="poll-container">

                    <!-- No active poll state -->
                    <div class="wc-empty-state py-5 text-center text-muted" id="poll-empty">
                        <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-50"></i>
                        <p class="small mb-0">No active poll right now.</p>
                    </div>

                    <!-- Active poll (hidden until JS populates) -->
                    <div id="poll-card" style="display:none;">
                        <p class="fw-semibold mb-3" id="poll-question"></p>
                        <form id="poll-form">
                            <input type="hidden" name="_csrf_token" value="<?= Sanitizer::e($csrfToken) ?>">
                            <div id="poll-options" class="mb-3"></div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check2-circle me-1"></i>Submit Vote
                            </button>
                        </form>
                        <!-- Results shown after voting -->
                        <div id="poll-results" style="display:none;">
                            <p class="text-success small text-center mb-2">
                                <i class="bi bi-check-circle-fill me-1"></i>Vote recorded!
                            </p>
                            <div id="poll-results-bars"></div>
                        </div>
                    </div>

                </div>
            </div><!-- /#pane-poll -->

            <!-- ── Quiz Pane ──────────────────────────────────────────────── -->
            <div class="tab-pane fade h-100 d-flex flex-column"
                 id="pane-quiz" role="tabpanel" aria-labelledby="tab-quiz">

                <div class="flex-grow-1 overflow-auto p-3" id="quiz-container">

                    <!-- No active quiz state -->
                    <div class="wc-empty-state py-5 text-center text-muted" id="quiz-empty">
                        <i class="bi bi-patch-question fs-2 d-block mb-2 opacity-50"></i>
                        <p class="small mb-0">No quiz is active right now.</p>
                    </div>

                    <!-- Active quiz info (hidden until JS populates) -->
                    <div id="quiz-card" style="display:none;">
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-patch-question-fill text-primary mt-1"></i>
                            <div>
                                <p class="fw-semibold mb-0" id="quiz-title"></p>
                                <p class="text-muted small mb-0" id="quiz-description"></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3 text-muted small" id="quiz-meta">
                            <i class="bi bi-clock"></i>
                            <span id="quiz-time-limit"></span>
                        </div>
                        <a id="quiz-start-link" href="#" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-play-circle me-1"></i>Start Quiz
                        </a>
                    </div>

                </div>
            </div><!-- /#pane-quiz -->

            <!-- ── Announcements Pane ─────────────────────────────────────── -->
            <div class="tab-pane fade h-100 d-flex flex-column"
                 id="pane-announce" role="tabpanel" aria-labelledby="tab-announce">

                <div class="flex-grow-1 overflow-auto p-3" id="announce-list">

                    <!-- No announcements state -->
                    <div class="wc-empty-state py-5 text-center text-muted" id="announce-empty">
                        <i class="bi bi-megaphone fs-2 d-block mb-2 opacity-50"></i>
                        <p class="small mb-0">No announcements yet.</p>
                    </div>

                    <!-- Announcement items injected by JS -->

                </div>
            </div><!-- /#pane-announce -->

        </div><!-- /.tab-content -->

    </aside><!-- /.wc-sidebar -->

</div><!-- /.wc-room -->

<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmLXleGMPAGnMUqH4JGekAXHFk6"
    crossorigin="anonymous"
></script>

<!-- Seed data for webcast.js -->
<script>
    <?= $inlineScript ?>
</script>

<!-- Webcast controller -->
<script src="/assets/js/webcast.js"></script>

</body>
</html>
