<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Registration;
use App\DTOs\RegistrationDTO;
use Core\Database\Database;

/**
 * RegistrationRepository — all SQL for the registrations table.
 * No SQL anywhere else. Called only by RegistrationService.
 */
class RegistrationRepository
{
    public function findById(int $id): ?Registration
    {
        $row = Database::queryOne(
            "SELECT * FROM registrations WHERE id = ? AND deleted_at IS NULL LIMIT 1",
            [$id]
        );
        return $row ? new Registration($row) : null;
    }

    public function findByCode(string $code): ?Registration
    {
        $row = Database::queryOne(
            "SELECT * FROM registrations WHERE registration_code = ? AND deleted_at IS NULL LIMIT 1",
            [$code]
        );
        return $row ? new Registration($row) : null;
    }

    public function findByEventAndEmail(int $eventId, string $email): ?Registration
    {
        $row = Database::queryOne(
            "SELECT * FROM registrations
             WHERE event_id = ?
               AND email = ?
               AND deleted_at IS NULL
             LIMIT 1",
            [$eventId, strtolower(trim($email))]
        );
        return $row ? new Registration($row) : null;
    }

    public function existsByEventAndEmail(int $eventId, string $email): bool
    {
        $row = Database::queryOne(
            "SELECT id FROM registrations
             WHERE event_id = ? AND email = ? AND deleted_at IS NULL LIMIT 1",
            [$eventId, strtolower(trim($email))]
        );
        return $row !== null;
    }

    public function create(RegistrationDTO $dto, string $attendeeId, string $approvalStatus): int
    {
        return Database::insert(
            "INSERT INTO registrations
                (event_id, form_id, registration_code, first_name, last_name,
                 email, phone, company, job_title, ip_address,
                 approval_status, status, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?,
                 ?, 'active', NOW(), NOW())",
            [
                $dto->eventId,
                $dto->formId,
                $attendeeId,
                $dto->firstName,
                $dto->lastName,
                strtolower(trim($dto->email)),
                $dto->mobile,
                $dto->company,
                $dto->designation,
                $dto->ipAddress,
                $approvalStatus,
            ]
        );
    }

    /**
     * Persist custom field answers as EAV rows.
     * Called inside the same transaction as create().
     */
    public function saveFieldValues(int $registrationId, array $fieldValues): void
    {
        foreach ($fieldValues as $fieldId => $value) {
            Database::execute(
                "INSERT INTO registration_field_values
                    (registration_id, field_id, value, created_at, updated_at)
                 VALUES (?, ?, ?, NOW(), NOW())",
                [$registrationId, $fieldId, (string) $value]
            );
        }
    }

    /**
     * Count all active registrations for an event.
     * Used by the service to enforce max_attendees cap.
     */
    public function countByEvent(int $eventId): int
    {
        $row = Database::queryOne(
            "SELECT COUNT(*) AS total FROM registrations
             WHERE event_id = ? AND status = 'active' AND deleted_at IS NULL",
            [$eventId]
        );
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Count all registrations ever created for the platform.
     * Used by AttendeeIdService to calculate the next sequential suffix.
     */
    public function totalCount(): int
    {
        $row = Database::queryOne("SELECT COUNT(*) AS total FROM registrations");
        return (int) ($row['total'] ?? 0);
    }

    public function isCodeUnique(string $code): bool
    {
        $row = Database::queryOne(
            "SELECT id FROM registrations WHERE registration_code = ? LIMIT 1",
            [$code]
        );
        return $row === null;
    }

    public function updateApprovalStatus(int $id, string $status, int $approvedBy): void
    {
        Database::execute(
            "UPDATE registrations
             SET approval_status = ?, approved_at = NOW(), approved_by = ?, updated_at = NOW()
             WHERE id = ?",
            [$status, $approvedBy, $id]
        );
    }

    public function softDelete(int $id): void
    {
        Database::execute(
            "UPDATE registrations SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function listByEvent(int $eventId, string $approvalStatus = ''): array
    {
        $sql    = "SELECT * FROM registrations WHERE event_id = ? AND deleted_at IS NULL";
        $params = [$eventId];

        if ($approvalStatus !== '') {
            $sql     .= " AND approval_status = ?";
            $params[] = $approvalStatus;
        }

        $sql .= " ORDER BY created_at DESC";

        $rows = Database::query($sql, $params);
        return array_map(fn($r) => new Registration($r), $rows);
    }
}
