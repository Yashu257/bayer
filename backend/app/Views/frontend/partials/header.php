<?php
/**
 * Partial: Site Header / Navigation
 *
 * Available variables (injected from layout scope):
 *   $event  — current Event model (optional, only on event pages)
 */

use Core\Security\Sanitizer;
use Core\Session\Session;

$isLandingPage = isset($event);
$authUser      = Session::get('auth_user');
?>
<header id="site-header" class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark" id="mainNav">
        <div class="container">

            <!-- Brand / logo -->
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-broadcast me-2"></i>PharmaWebcast
            </a>

            <!-- Mobile toggle -->
            <button
                class="navbar-toggler border-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContent"
                aria-controls="navbarContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav links -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                    <?php if ($isLandingPage): ?>
                    <!-- Event-contextual nav -->
                    <li class="nav-item">
                        <a class="nav-link" href="#about">
                            <i class="bi bi-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#agenda">
                            <i class="bi bi-calendar3 me-1"></i>Agenda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#speakers">
                            <i class="bi bi-people me-1"></i>Speakers
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($authUser): ?>
                    <!-- Authenticated user menu -->
                    <li class="nav-item dropdown ms-lg-2">
                        <button
                            class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-person-circle"></i>
                            <span><?= Sanitizer::e($authUser['email'] ?? 'Account') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="/profile">
                                    <i class="bi bi-person me-2"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/profile/events">
                                    <i class="bi bi-calendar-check me-2"></i>My Events
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/logout" method="POST" class="d-inline">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= Sanitizer::e(\Core\Security\CsrfGuard::token()) ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    <?php else: ?>
                    <!-- Guest actions -->
                    <li class="nav-item ms-lg-1">
                        <a href="/login"
                           class="btn btn-sm btn-outline-light px-3"
                           role="button">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <?php if ($isLandingPage && isset($event) && $event->registrationOpen()): ?>
                    <li class="nav-item ms-lg-1">
                        <a href="/e/<?= Sanitizer::e($event->slug) ?>/register"
                           class="btn btn-sm btn-primary px-3"
                           role="button">
                            <i class="bi bi-pencil-square me-1"></i>Register
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                </ul>
            </div><!-- /.navbar-collapse -->

        </div><!-- /.container -->
    </nav>
</header>
