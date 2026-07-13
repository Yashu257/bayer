<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= \Core\Security\Sanitizer::e($pageTitle ?? 'Admin') ?> — PharmaWebcast Admin</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js (loaded here so it's available to admin.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- Admin styles -->
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Mobile overlay -->
<div id="adm-overlay" class="adm-overlay"></div>

<div class="adm-shell">

    <!-- ═══ SIDEBAR ══════════════════════════════════════════════════════ -->
    <aside class="adm-sidebar" id="adm-sidebar" aria-label="Admin navigation">

        <!-- Logo -->
        <a href="/admin/dashboard" class="adm-logo">
            <span class="adm-logo-icon"><i class="bi bi-broadcast"></i></span>
            <span class="adm-logo-text">PharmaWebcast</span>
            <span class="adm-logo-badge">Admin</span>
        </a>

        <!-- Navigation -->
        <nav class="adm-nav">

            <div class="adm-nav-section">
                <p class="adm-nav-heading">Overview</p>
                <a href="/admin/dashboard"
                   class="adm-nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a href="/admin/events"
                   class="adm-nav-link <?= ($activePage ?? '') === 'events' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i> Events
                </a>
            </div>

            <div class="adm-nav-section">
                <p class="adm-nav-heading">Attendees</p>
                <a href="/admin/users"
                   class="adm-nav-link <?= ($activePage ?? '') === 'attendees' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> All Attendees
                </a>
                <a href="/admin/reports"
                   class="adm-nav-link <?= ($activePage ?? '') === 'registrations' ? 'active' : '' ?>">
                    <i class="bi bi-person-check"></i> Registration Reports
                </a>
            </div>

            <div class="adm-nav-section">
                <p class="adm-nav-heading">Live Event</p>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'questions' ? 'active' : '' ?>">
                    <i class="bi bi-chat-square-text"></i> Questions
                    <?php if (!empty($pendingQuestions)): ?>
                        <span class="adm-nav-badge"><?= (int)$pendingQuestions ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'polls' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i> Polls
                </a>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'quiz' ? 'active' : '' ?>">
                    <i class="bi bi-patch-question"></i> Quiz
                </a>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'surveys' ? 'active' : '' ?>">
                    <i class="bi bi-ui-checks"></i> Surveys
                </a>
            </div>

            <div class="adm-nav-section">
                <p class="adm-nav-heading">Analytics</p>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">
                    <i class="bi bi-eye"></i> Attendance
                </a>
                <a href="#" class="adm-nav-link <?= ($activePage ?? '') === 'emails' ? 'active' : '' ?>">
                    <i class="bi bi-envelope-check"></i> Email Status
                </a>
                <a href="/admin/activity-logs"
                   class="adm-nav-link <?= ($activePage ?? '') === 'activity-logs' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Activity Logs
                </a>
            </div>

            <div class="adm-nav-section">
                <p class="adm-nav-heading">Configuration</p>
                <a href="/admin/brands"
                   class="adm-nav-link <?= ($activePage ?? '') === 'branding' ? 'active' : '' ?>">
                    <i class="bi bi-palette"></i> Branding
                </a>
                <a href="/admin/settings"
                   class="adm-nav-link <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i> Settings
                </a>
                <a href="/admin/admins"
                   class="adm-nav-link <?= ($activePage ?? '') === 'admins' ? 'active' : '' ?>">
                    <i class="bi bi-shield-check"></i> Admin Users
                </a>
            </div>

        </nav>

        <!-- Footer -->
        <div class="adm-sidebar-footer">
            <div class="adm-user-chip">
                <?php $admin = \Core\Session\Session::get('auth_admin'); ?>
                <div class="adm-user-avatar">
                    <?= strtoupper(substr($admin['email'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <div class="text-truncate" style="color:#f1f5f9;font-size:.8125rem;font-weight:600;">
                        <?= \Core\Security\Sanitizer::e($admin['email'] ?? 'Admin') ?>
                    </div>
                    <div style="font-size:.7rem;">Super Admin</div>
                </div>
            </div>
        </div>

    </aside><!-- /.adm-sidebar -->

    <!-- ═══ MAIN ══════════════════════════════════════════════════════════ -->
    <div class="adm-main">

        <!-- Topbar -->
        <header class="adm-topbar">
            <button class="adm-topbar-toggle" id="adm-toggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>

            <div class="adm-breadcrumb">
                <a href="/admin/dashboard" class="text-decoration-none text-muted">Admin</a>
                <?php if (!empty($pageTitle) && $pageTitle !== 'Dashboard'): ?>
                    <span class="mx-1 text-muted">/</span>
                    <span class="crumb"><?= \Core\Security\Sanitizer::e($pageTitle) ?></span>
                <?php endif; ?>
            </div>

            <div class="adm-topbar-actions">
                <a href="/admin/events" class="adm-topbar-btn" title="Events">
                    <i class="bi bi-calendar-event"></i>
                </a>
                <a href="#" class="adm-topbar-btn adm-topbar-notif" title="Notifications">
                    <i class="bi bi-bell"></i>
                </a>
                <a href="/" class="adm-topbar-btn" title="View site" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>

                <!-- Logout -->
                <form method="POST" action="/admin/logout" class="mb-0">
                    <input type="hidden" name="_csrf_token"
                           value="<?= \Core\Security\Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                    <button type="submit" class="adm-topbar-btn" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash messages -->
        <?php if (\Core\Session\Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible adm-alert-auto mx-3 mt-3 mb-0" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= \Core\Security\Sanitizer::e(\Core\Session\Session::getFlash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (\Core\Session\Session::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible adm-alert-auto mx-3 mt-3 mb-0" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= \Core\Security\Sanitizer::e(\Core\Session\Session::getFlash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (\Core\Session\Session::hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible adm-alert-auto mx-3 mt-3 mb-0" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= \Core\Security\Sanitizer::e(\Core\Session\Session::getFlash('warning')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page content -->
        <div class="adm-content">
            <?= $content ?>
        </div>

    </div><!-- /.adm-main -->

</div><!-- /.adm-shell -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmLXleGMPAGnMUqH4JGekAXHFk6"
        crossorigin="anonymous"></script>

<!-- Chart seed data (injected by controller) -->
<?php if (!empty($inlineScript)): ?>
<script><?= $inlineScript ?></script>
<?php endif; ?>

<!-- Admin JS -->
<script src="/assets/js/admin.js"></script>

</body>
</html>
