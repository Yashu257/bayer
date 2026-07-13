<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * RegistrationDTO — typed, immutable payload passed between layers.
 * Created by the validator; consumed by the service.
 * No SQL. No HTTP. No business logic.
 */
final class RegistrationDTO
{
    public function __construct(
        public readonly int    $eventId,
        public readonly int    $formId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $mobile,
        public readonly string $company,
        public readonly string $designation,
        public readonly string $city,
        public readonly string $state,
        public readonly string $country,
        public readonly bool   $consentGiven,
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function toArray(): array
    {
        return [
            'event_id'      => $this->eventId,
            'form_id'       => $this->formId,
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'email'         => $this->email,
            'mobile'        => $this->mobile,
            'company'       => $this->company,
            'designation'   => $this->designation,
            'city'          => $this->city,
            'state'         => $this->state,
            'country'       => $this->country,
            'consent_given' => $this->consentGiven,
            'ip_address'    => $this->ipAddress,
            'user_agent'    => $this->userAgent,
        ];
    }
}
