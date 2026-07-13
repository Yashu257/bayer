<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Repositories\RegistrationRepository;
use App\StreamingProviders\StreamingProviderFactory;
use Core\Database\Database;
use Core\Exceptions\HttpException;

/**
 * WebcastService — assembles all data needed to render the webcast room.
 *
 * This is the single place the controller calls. It returns a plain array
 * that gets JSON-encoded into window.WEBCAST and used by webcast.js.
 *
 * Responsibilities:
 *   - Resolve streaming provider config (provider-agnostic)
 *   - Load initial Q&A questions (latest 20, approved/visible)
 *   - Load active poll (if any)
 *   - Load active quiz (if any)
 *   - Load latest announcements (if any)
 *   - Record attendance start (INSERT IGNORE into attendance_logs)
 *
 * What this class does NOT do:
 *   - No HTTP (no Request/Response)
 *   - No HTML output
 *   - No auth logic (auth is resolved upstream by middleware)
 */
class WebcastService
{
    public function buildRoomData(Event $event, Registration $registration): array
    {
        // ── 1. Streaming config ────────────────────────────────────────────────
        $streamConfig = StreamingProviderFactory::make($event)->getEmbedConfig();

        // ── 2. Initial Q&A (latest 20 approved questions for sidebar) ─────────
        $questions = $this->loadQuestions((int) $event->id);

        // ── 3. Active poll ─────────────────────────────────────────────────────
        $poll = $this->loadActivePoll((int) $event->id);

        // ── 4. Active quiz ─────────────────────────────────────────────────────
        $quiz = $this->loadActiveQuiz((int) $event->id);

        // ── 5. Latest announcements ────────────────────────────────────────────
        $announcements = $this->loadAnnouncements((int) $event->id);

        // ── 6. Attendance record ───────────────────────────────────────────────
        $this->recordAttendanceStart((int) $event->id, (int) $registration->id);

        return [
            'event'         => [
                'id'    => $event->id,
                'title' => $event->title,
                'slug'  => $event->slug,
            ],
            'stream'        => $streamConfig,
            'attendee'      => [
                'id'          => $registration->id,
                'attendeeId'  => $registration->attendee_id,
                'name'        => $registration->full_name,
            ],
            'sidebar'       => [
                'questions'     => $questions,
                'poll'          => $poll,
                'quiz'          => $quiz,
                'announcements' => $announcements,
            ],
            'heartbeatUrl'  => '/e/' . $event->slug . '/watch/heartbeat',
            'heartbeatEvery' => 60,    // seconds
        ];
    }

    public function recordHeartbeat(int $eventId, int $registrationId): void
    {
        Database::execute(
            'UPDATE attendance_logs
                SET last_ping_at = NOW(), watch_seconds = watch_seconds + 60
              WHERE event_id = ? AND registration_id = ?',
            [$eventId, $registrationId]
        );
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function loadQuestions(int $eventId): array
    {
        return Database::query(
            'SELECT id, question_text, asked_by_name, upvote_count, is_answered, created_at
               FROM questions
              WHERE event_id = ? AND status = "approved"
              ORDER BY is_answered ASC, upvote_count DESC, created_at DESC
              LIMIT 20',
            [$eventId]
        );
    }

    private function loadActivePoll(int $eventId): ?array
    {
        $poll = Database::queryOne(
            'SELECT id, question, status
               FROM polls
              WHERE event_id = ? AND status = "active"
              ORDER BY created_at DESC
              LIMIT 1',
            [$eventId]
        );

        if ($poll === null) {
            return null;
        }

        $options = Database::query(
            'SELECT id, option_text, vote_count
               FROM poll_options
              WHERE poll_id = ?
              ORDER BY display_order ASC',
            [(int) $poll['id']]
        );

        return array_merge($poll, ['options' => $options]);
    }

    private function loadActiveQuiz(int $eventId): ?array
    {
        return Database::queryOne(
            'SELECT id, title, description, time_limit_seconds, status
               FROM quizzes
              WHERE event_id = ? AND status = "active"
              ORDER BY created_at DESC
              LIMIT 1',
            [$eventId]
        );
    }

    private function loadAnnouncements(int $eventId): array
    {
        return Database::query(
            'SELECT id, message, created_at
               FROM announcements
              WHERE event_id = ? AND status = "active"
              ORDER BY created_at DESC
              LIMIT 10',
            [$eventId]
        );
    }

    private function recordAttendanceStart(int $eventId, int $registrationId): void
    {
        Database::execute(
            'INSERT IGNORE INTO attendance_logs
                (event_id, registration_id, joined_at, last_ping_at, watch_seconds)
             VALUES (?, ?, NOW(), NOW(), 0)',
            [$eventId, $registrationId]
        );
    }
}
