<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Core\Database\Database;
use Core\Http\Request;
use Core\Http\Response;
use Core\Security\Sanitizer;
use Core\Session\Session;

class ProfileController extends BaseController
{
    public function show(Request $request): Response
    {
        $user = Session::get('auth_user');

        return $this->view('frontend.profile.show', [
            'user'      => $user,
            'pageTitle' => 'My Profile',
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = Session::get('auth_user');

        return $this->view('frontend.profile.edit', [
            'user'      => $user,
            'pageTitle' => 'Edit Profile',
        ]);
    }

    public function update(Request $request): Response
    {
        $user  = Session::get('auth_user');
        $data  = $request->only(['first_name', 'last_name', 'phone', 'specialty', 'institution']);
        $clean = array_map([Sanitizer::class, 'string'], $data);

        Database::execute(
            'UPDATE users SET first_name=?, last_name=?, phone=?, specialty=?, institution=?, updated_at=NOW()
              WHERE id = ?',
            array_merge(array_values($clean), [$user['id']])
        );

        Session::set('auth_user', array_merge($user, $clean));
        Session::flash('success', 'Profile updated successfully.');

        return Response::redirect('/profile');
    }

    public function updatePassword(Request $request): Response
    {
        $user        = Session::get('auth_user');
        $current     = $request->input('current_password', '');
        $newPassword = $request->input('new_password', '');
        $confirm     = $request->input('confirm_password', '');

        $row = Database::queryOne('SELECT password FROM users WHERE id = ?', [$user['id']]);

        if (!$row || !password_verify($current, $row['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            return Response::redirect('/profile/password');
        }

        if ($newPassword !== $confirm || strlen($newPassword) < 8) {
            Session::flash('error', 'New passwords do not match or are too short.');
            return Response::redirect('/profile/password');
        }

        Database::execute(
            'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
            [password_hash($newPassword, PASSWORD_BCRYPT), $user['id']]
        );

        Session::flash('success', 'Password changed successfully.');
        return Response::redirect('/profile');
    }

    public function myEvents(Request $request): Response
    {
        $user = Session::get('auth_user');

        $registrations = Database::query(
            'SELECT r.*, e.title, e.starts_at, e.status AS event_status, e.slug
               FROM registrations r
               JOIN events e ON e.id = r.event_id
              WHERE r.user_id = ?
              ORDER BY e.starts_at DESC',
            [$user['id']]
        );

        return $this->view('frontend.profile.my-events', [
            'registrations' => $registrations,
            'pageTitle'     => 'My Events',
        ]);
    }

    public function certificate(Request $request): Response
    {
        $user           = Session::get('auth_user');
        $registrationId = (int) $request->param('id');

        $registration = Database::queryOne(
            'SELECT r.*, e.title, e.starts_at, e.cme_credits
               FROM registrations r
               JOIN events e ON e.id = r.event_id
              WHERE r.id = ? AND r.user_id = ? AND r.approval_status = \'approved\'',
            [$registrationId, $user['id']]
        );

        if (!$registration) {
            return Response::redirect('/profile/events');
        }

        return $this->view('frontend.profile.certificate', [
            'registration' => $registration,
            'user'         => $user,
            'pageTitle'    => 'CME Certificate',
        ]);
    }
}
