<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RegistrationForm;
use Core\Database\Database;

class RegistrationFormRepository
{
    public function findByEventId(int $eventId): ?RegistrationForm
    {
        $row = Database::queryOne(
            "SELECT * FROM registration_forms
             WHERE event_id = ? AND status = 'active'
             LIMIT 1",
            [$eventId]
        );
        return $row ? new RegistrationForm($row) : null;
    }

    /**
     * Return all active custom fields for a form, ordered by sort_order.
     */
    public function fieldsByForm(int $formId): array
    {
        return Database::query(
            "SELECT * FROM registration_form_fields
             WHERE form_id = ? AND status = 'active'
             ORDER BY sort_order ASC",
            [$formId]
        );
    }
}
