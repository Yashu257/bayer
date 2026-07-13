<?php

declare(strict_types=1);

namespace App\Validators;

use App\DTOs\RegistrationDTO;
use App\Repositories\RegistrationRepository;
use Core\Exceptions\ValidationException;
use Core\Security\Sanitizer;

/**
 * RegistrationValidator — dedicated validation layer for registration forms.
 *
 * Responsibilities:
 *   1. Field-level rules (required, format, length)
 *   2. Business rules (duplicate email per event, capacity)
 *   3. Consent verification
 *   4. Returns a validated, typed RegistrationDTO on success
 *
 * No SQL directly — duplicate check delegates to the repository.
 * No HTTP — receives raw array, returns DTO.
 */
class RegistrationValidator
{
    private array $errors  = [];
    private readonly RegistrationRepository $registrations;

    public function __construct()
    {
        $this->registrations = new RegistrationRepository();
    }

    /**
     * Validate raw POST input against a specific event.
     *
     * @param array $input   Raw $_POST data
     * @param int   $eventId Target event
     * @param int   $formId  Active registration form
     * @param int   $maxCapacity  0 = unlimited
     *
     * @throws ValidationException on any failure
     */
    public function validate(
        array $input,
        int   $eventId,
        int   $formId,
        int   $maxCapacity,
        string $ipAddress,
        string $userAgent,
    ): RegistrationDTO {

        // --- Field rules -----------------------------------------------------

        $firstName   = $this->validateRequired($input, 'first_name',   'First Name',   max: 100);
        $lastName    = $this->validateRequired($input, 'last_name',    'Last Name',    max: 100);
        $email       = $this->validateEmail($input);
        $mobile      = $this->validateMobile($input);
        $company     = $this->validateRequired($input, 'company',      'Company',      max: 200);
        $designation = $this->validateRequired($input, 'designation',  'Designation',  max: 150);
        $city        = $this->validateRequired($input, 'city',         'City',         max: 100);
        $state       = $this->validateRequired($input, 'state',        'State',        max: 100);
        $country     = $this->validateRequired($input, 'country',      'Country',      max: 100);
        $consent     = $this->validateConsent($input);

        // --- Fail fast if field errors exist ---------------------------------
        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        // --- Business rules (only run if fields are valid) -------------------

        // Duplicate email per event
        if ($email !== '' && $this->registrations->existsByEventAndEmail($eventId, $email)) {
            $this->errors['email'][] = 'This email address is already registered for this event.';
        }

        // Capacity check
        if ($maxCapacity > 0) {
            $current = $this->registrations->countByEvent($eventId);
            if ($current >= $maxCapacity) {
                $this->errors['_form'][] = 'This event has reached maximum capacity. Registration is closed.';
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        // --- Return typed DTO ------------------------------------------------
        return new RegistrationDTO(
            eventId:     $eventId,
            formId:      $formId,
            firstName:   $firstName,
            lastName:    $lastName,
            email:       strtolower($email),
            mobile:      $mobile,
            company:     $company,
            designation: $designation,
            city:        $city,
            state:       $state,
            country:     $country,
            consentGiven: $consent,
            ipAddress:   $ipAddress,
            userAgent:   $userAgent,
        );
    }

    // --- Field validators ----------------------------------------------------

    private function validateRequired(
        array  $input,
        string $field,
        string $label,
        int    $max = 255,
        int    $min = 1
    ): string {
        $value = Sanitizer::cleanString((string) ($input[$field] ?? ''));

        if ($value === '') {
            $this->errors[$field][] = "$label is required.";
            return '';
        }

        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = "$label must be at least $min character(s).";
        }

        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = "$label must not exceed $max characters.";
        }

        return $value;
    }

    private function validateEmail(array $input): string
    {
        $raw   = Sanitizer::cleanString((string) ($input['email'] ?? ''));
        $value = strtolower($raw);

        if ($value === '') {
            $this->errors['email'][] = 'Email address is required.';
            return '';
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'][] = 'Please enter a valid email address.';
            return '';
        }

        if (mb_strlen($value) > 180) {
            $this->errors['email'][] = 'Email address must not exceed 180 characters.';
            return '';
        }

        return $value;
    }

    private function validateMobile(array $input): string
    {
        $value = Sanitizer::cleanString((string) ($input['mobile'] ?? ''));

        if ($value === '') {
            $this->errors['mobile'][] = 'Mobile number is required.';
            return '';
        }

        // Allow digits, spaces, +, -, (, ) — standard international formats
        if (!preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $value)) {
            $this->errors['mobile'][] = 'Please enter a valid mobile number (7–20 digits).';
        }

        return $value;
    }

    private function validateConsent(array $input): bool
    {
        $checked = isset($input['consent']) && in_array($input['consent'], ['1', 'on', 'true', true], true);

        if (!$checked) {
            $this->errors['consent'][] = 'You must agree to the terms and privacy policy to register.';
        }

        return $checked;
    }
}
