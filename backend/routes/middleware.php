<?php

/**
 * MIDDLEWARE REGISTRY
 *
 * Maps every middleware key (used in route definitions) to its class.
 * The Router reads this map and resolves the middleware stack per request.
 *
 * Execution order per request:
 *   1. Global stack  — always runs, regardless of route
 *   2. Group stack   — runs for a named group (web / admin / api)
 *   3. Route stack   — explicit middleware listed on the route definition
 *
 * Resolution: the Router builds a pipeline and calls each middleware in order.
 * Each middleware either passes the request to the next handler or terminates
 * early with a Response (redirect, JSON error, 403, etc.).
 */

return [

    // =========================================================================
    // GLOBAL MIDDLEWARE STACK
    // Runs on every single request before route resolution.
    // =========================================================================
    'global' => [
        \App\Middleware\TrimStringsMiddleware::class,        // strip leading/trailing whitespace from inputs
        \App\Middleware\MaintenanceModeMiddleware::class,    // return 503 if maintenance flag is set
        \App\Middleware\SecurityHeadersMiddleware::class,    // add X-Frame-Options, CSP, X-XSS-Protection, etc.
        \App\Middleware\RequestLoggingMiddleware::class,     // log every inbound request to activity_logs
    ],

    // =========================================================================
    // GROUP MIDDLEWARE
    // Applied automatically to all routes within a named group.
    // =========================================================================
    'groups' => [

        // Web group — HTML page responses
        'web' => [
            \App\Middleware\SessionStartMiddleware::class,   // start / resume PHP session
            \App\Middleware\CsrfMiddleware::class,           // validate _csrf_token on POST/PUT/DELETE
            \App\Middleware\XssCleanMiddleware::class,       // sanitise all input against XSS
        ],

        // Admin group — back-office panel
        'admin' => [
            \App\Middleware\SessionStartMiddleware::class,
            \App\Middleware\CsrfMiddleware::class,
            \App\Middleware\XssCleanMiddleware::class,
            \App\Middleware\AdminIpWhitelistMiddleware::class, // optional: restrict admin to known IPs
        ],

        // API group — stateless JSON responses
        'api' => [
            \App\Middleware\JsonResponseMiddleware::class,   // force Content-Type: application/json
            \App\Middleware\XssCleanMiddleware::class,
            // No session or CSRF — uses Bearer token auth instead
        ],
    ],

    // =========================================================================
    // NAMED MIDDLEWARE
    // Referenced by key in route definitions.
    // =========================================================================
    'named' => [

        // --- Authentication --------------------------------------------------
        'auth'              => \App\Middleware\AuthMiddleware::class,
        // Checks user_sessions table for a valid non-expired token / session.
        // Redirects to /login on failure.

        'guest'             => \App\Middleware\GuestMiddleware::class,
        // Redirects to /profile if user is already authenticated.

        'verified'          => \App\Middleware\EmailVerifiedMiddleware::class,
        // Checks users.email_verified_at is not NULL.
        // Redirects to verification notice page on failure.

        'admin.auth'        => \App\Middleware\AdminAuthMiddleware::class,
        // Checks admin_sessions table. Redirects to /admin/login on failure.

        'admin.guest'       => \App\Middleware\AdminGuestMiddleware::class,
        // Redirects to /admin/dashboard if admin is already logged in.

        'api.auth'          => \App\Middleware\ApiAuthMiddleware::class,
        // Validates Authorization: Bearer <token> against user_sessions.
        // Returns 401 JSON on failure.

        'api.admin'         => \App\Middleware\ApiAdminAuthMiddleware::class,
        // Validates Authorization: Bearer <token> against admin_sessions.
        // Returns 401 JSON on failure.

        // --- Authorisation ---------------------------------------------------
        'admin.role'        => \App\Middleware\AdminRoleMiddleware::class,
        // Usage: admin.role:super_admin  or  admin.role:admin
        // Reads the role parameter and checks admins.role_id against roles.slug.
        // Returns 403 on insufficient privilege.

        'registered'        => \App\Middleware\EventRegistrationMiddleware::class,
        // Checks that the authenticated user has an approved registration
        // for the event resolved in the current request.
        // Returns 403 or redirect to registration page.

        // --- Event Resolution ------------------------------------------------
        'event'             => \App\Middleware\ResolveEventMiddleware::class,
        // Resolves {slug} to an events row and binds it to the request object.
        // Returns 404 if event not found.
        // Returns 403 if event is private and password not provided.

        // --- Security --------------------------------------------------------
        'csrf'              => \App\Middleware\CsrfMiddleware::class,
        // Explicit CSRF check — same class as group middleware but can be
        // applied individually to specific routes.

        'throttle'          => \App\Middleware\ThrottleMiddleware::class,
        // Usage: throttle:MAX_ATTEMPTS,DECAY_MINUTES  e.g. throttle:5,1
        // Tracks hits per IP + route in cache. Returns 429 on breach.

        // --- Utility ---------------------------------------------------------
        'maintenance'       => \App\Middleware\MaintenanceModeMiddleware::class,
        // Bypass key: ?bypass=SECRET defined in settings.
    ],

    // =========================================================================
    // MIDDLEWARE EXECUTION ORDER (within the named stack per route)
    // The Router applies named middleware in the order listed on each route.
    // Typical order for a protected web route:
    //
    //   [global] → SessionStart → CsrfCheck → XssClean
    //            → event (resolve slug) → auth → verified → registered
    //            → Controller@method
    // =========================================================================
];
