<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Repositories\EventRepository;
use App\Services\RegistrationService;
use Core\Http\Request;
use Core\Http\Response;
use Core\Security\CsrfGuard;
use Core\Session\Session;
use Core\Validation\ValidationException;

/**
 * RegistrationController — handles the attendee registration flow.
 *
 * Routes handled (see routes/web.php):
 *   GET  /event/{slug}/register          → showForm()
 *   POST /event/{slug}/register          → submit()
 *   GET  /event/{slug}/register/confirm  → confirm()
 *   GET  /event/{slug}/register/pending  → pending()
 */
class RegistrationController extends BaseController
{
    private readonly RegistrationService $service;
    private readonly EventRepository     $eventRepo;

    public function __construct()
    {
        $this->service   = new RegistrationService();
        $this->eventRepo = new EventRepository();
    }

    /**
     * Show the registration form.
     */
    public function showForm(Request $request): mixed
    {
        $slug  = $request->getAttribute('slug');
        $event = $this->resolveOpenEvent($slug);

        return $this->view('frontend/registration/form', [
            'event'      => $event,
            'errors'     => Session::getFlash('errors', []),
            'old'        => Session::getFlash('old', []),
            'csrfToken'  => CsrfGuard::token(),
            'pageTitle'  => 'Register — ' . htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8'),
        ]);
    }

    /**
     * Handle the registration form submission.
     */
    public function submit(Request $request): mixed
    {
        $slug  = $request->getAttribute('slug');
        $event = $this->resolveOpenEvent($slug);

        try {
            $result = $this->service->register(
                input:     $request->all(),
                event:     $event,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            // Stash result for the confirm/pending page
            Session::set('registration_result', [
                'attendeeId'       => $result['attendeeId'],
                'requiresApproval' => $result['requiresApproval'],
                'name'             => $result['registration']->full_name,
                'email'            => $result['registration']->email,
            ]);

            $redirect = $result['requiresApproval']
                ? '/event/' . $slug . '/register/pending'
                : '/event/' . $slug . '/register/confirm';

            return Response::redirect($redirect);

        } catch (ValidationException $e) {
            Session::flash('errors', $e->getErrors());
            Session::flash('old', $request->only([
                'first_name', 'last_name', 'email', 'mobile',
                'company', 'designation', 'city', 'state', 'country',
            ]));

            return Response::redirect('/event/' . $slug . '/register');
        }
    }

    /**
     * Show the registration confirmation page (immediate approval).
     */
    public function confirm(Request $request): mixed
    {
        $result = Session::get('registration_result');

        if ($result === null || ($result['requiresApproval'] ?? false)) {
            return Response::redirect('/');
        }

        $slug  = $request->getAttribute('slug');
        $event = $this->eventRepo->findBySlug($slug);

        // Consume the session key so refreshing doesn't re-show stale data
        Session::remove('registration_result');

        return $this->view('frontend/registration/confirm', [
            'event'      => $event,
            'attendeeId' => $result['attendeeId'],
            'name'       => $result['name'],
            'email'      => $result['email'],
            'pageTitle'  => 'Registration Confirmed',
        ]);
    }

    /**
     * Show the pending-approval page.
     */
    public function pending(Request $request): mixed
    {
        $result = Session::get('registration_result');

        if ($result === null || !($result['requiresApproval'] ?? false)) {
            return Response::redirect('/');
        }

        $slug  = $request->getAttribute('slug');
        $event = $this->eventRepo->findBySlug($slug);

        Session::remove('registration_result');

        return $this->view('frontend/registration/pending', [
            'event'     => $event,
            'name'      => $result['name'],
            'email'     => $result['email'],
            'pageTitle' => 'Registration Pending',
        ]);
    }

    // -----------------------------------------------------------------------

    /**
     * Load and validate the event is open for registration.
     * Redirects to 404 if not found or not open.
     */
    private function resolveOpenEvent(string $slug): \App\Models\Event
    {
        $event = $this->eventRepo->findBySlug($slug);

        if ($event === null || !$event->registrationOpen()) {
            Response::redirect('/404')->send();
        }

        return $event;
    }
}
