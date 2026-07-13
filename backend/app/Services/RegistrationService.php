<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RegistrationDTO;
use App\Events\EventDispatcher;
use App\Events\RegistrationCompleted;
use App\Listeners\SendRegistrationConfirmationEmail;
use App\Models\Event;
use App\Models\Registration;
use App\Repositories\RegistrationRepository;
use App\Validators\RegistrationValidator;
use Core\Database\Database;

/**
 * RegistrationService — orchestrates the registration flow.
 *
 * Responsibilities:
 *   1. Run the validation + business-rules layer (RegistrationValidator).
 *   2. Generate a unique attendee ID (AttendeeIdService).
 *   3. Persist the registration inside a DB transaction.
 *   4. Dispatch the RegistrationCompleted event so listeners (email) can react.
 *
 * What this class does NOT do:
 *   - It does not touch HTTP — no Request, no Response, no session.
 *   - It does not send email directly — that is the listener's job.
 *   - It does not contain SQL — that belongs in the repository.
 */
class RegistrationService
{
    private readonly RegistrationRepository $registrationRepo;
    private readonly AttendeeIdService      $attendeeIdService;
    private readonly RegistrationValidator  $validator;

    public function __construct()
    {
        $this->registrationRepo  = new RegistrationRepository();
        $this->attendeeIdService = new AttendeeIdService();
        $this->validator         = new RegistrationValidator($this->registrationRepo);

        // Wire listeners exactly once here so the service is self-contained.
        EventDispatcher::listen(
            RegistrationCompleted::class,
            SendRegistrationConfirmationEmail::class
        );
    }

    /**
     * Process an attendee registration.
     *
     * @param array  $input       Raw POST data from the controller
     * @param Event  $event       The resolved Event model
     * @param string $ipAddress   Registrant's IP (for audit + throttle)
     * @param string $userAgent   Registrant's UA (for audit)
     *
     * @return array{registration: Registration, attendeeId: string, requiresApproval: bool}
     *
     * @throws \Core\Validation\ValidationException  When field/business rules fail
     * @throws \RuntimeException                     On DB failure (transaction rolled back)
     */
    public function register(array $input, Event $event, string $ipAddress, string $userAgent): array
    {
        // 1 — Validate + build typed DTO (throws ValidationException on failure)
        $dto = $this->validator->validate(
            input:      $input,
            eventId:    $event->id,
            formId:     $event->registration_form_id,
            ipAddress:  $ipAddress,
            userAgent:  $userAgent,
        );

        // 2 — Determine approval flow from the form config
        $requiresApproval = (bool) ($event->requiresApproval ?? false);
        $approvalStatus   = $requiresApproval ? 'pending' : 'approved';

        // 3 — Generate attendee ID before the transaction (avoids locking issues)
        $attendeeId = $this->attendeeIdService->generate();

        // 4 — Persist inside a transaction; roll back on any exception
        Database::beginTransaction();

        try {
            $registrationId = $this->registrationRepo->create($dto, $attendeeId, $approvalStatus);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }

        // 5 — Load the persisted model so downstream code and events have a full object
        $registration = $this->registrationRepo->findById($registrationId);

        // 6 — Dispatch the domain event (email confirmation, future: webhooks, etc.)
        EventDispatcher::dispatch(new RegistrationCompleted(
            registration:     $registration,
            event:            $event,
            attendeeId:       $attendeeId,
            requiresApproval: $requiresApproval,
        ));

        return compact('registration', 'attendeeId', 'requiresApproval');
    }
}
