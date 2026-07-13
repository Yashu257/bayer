<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;
use App\Repositories\RegistrationRepository;

/**
 * EventRegistrationMiddleware
 *
 * Verifies that the authenticated user has an APPROVED registration
 * for the event bound to the current request.
 *
 * Depends on (must run after):
 *   - AuthMiddleware         → sets request attribute 'auth_user'
 *   - ResolveEventMiddleware → sets request attribute 'event'
 *
 * On success: binds 'registration' to the request for downstream controllers.
 * On failure: redirects to the event registration page (or returns 403 for AJAX).
 *
 * The link between user account and registration is the email address.
 * Attendees register with an email; that email matches their user account.
 */
class EventRegistrationMiddleware
{
    public function handle(Request $request, callable $next, ?string $param = null): mixed
    {
        $user  = $request->getAttribute('auth_user');
        $event = $request->getAttribute('event');

        if ($user === null || $event === null) {
            // Defensive — should not happen if middleware order is correct
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Unauthorised.', 401);
            }
            return Response::redirect('/login');
        }

        $repo         = new RegistrationRepository();
        $registration = $repo->findByEventAndEmail((int) $event->id, $user->email);

        if ($registration === null) {
            // Not registered for this event at all
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('You are not registered for this event.', 403);
            }
            Session::flash('info', 'Please register to access this event.');
            return Response::redirect('/e/' . $event->slug . '/register');
        }

        if ($registration->approval_status === 'pending') {
            // Registered but awaiting organiser approval
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Your registration is pending approval.', 403);
            }
            Session::flash('warning', 'Your registration is pending organiser approval.');
            return Response::redirect('/e/' . $event->slug . '/register/pending');
        }

        if ($registration->approval_status !== 'approved') {
            // Rejected or other non-approved state
            if ($request->isAjax() || $request->isJson()) {
                return Response::error('Access denied.', 403);
            }
            Session::flash('error', 'You do not have access to this event.');
            return Response::redirect('/events');
        }

        // Bind for downstream use (controllers, heartbeat, Q&A, etc.)
        $request->setAttribute('registration', $registration);

        return $next($request);
    }
}
