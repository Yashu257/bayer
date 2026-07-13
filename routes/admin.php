<?php

/**
 * ADMIN ROUTES — Back-office Panel
 *
 * All routes prefixed with /admin
 *
 * Middleware keys:
 *   admin.auth    — require authenticated admin session
 *   admin.guest   — redirect if admin already logged in
 *   admin.role    — role-based access (e.g. admin.role:super_admin)
 *   csrf          — CSRF token validation on all state-mutating requests
 *   throttle      — rate limiter
 */

// =============================================================================
// ADMIN AUTH — GUEST ONLY
// =============================================================================

$router->get( '/admin/login',               'Admin\AuthController@showLogin',            ['admin.guest']);
$router->post('/admin/login',               'Admin\AuthController@login',                ['admin.guest', 'csrf', 'throttle:5,1']);
$router->post('/admin/logout',              'Admin\AuthController@logout',               ['admin.auth', 'csrf']);
$router->get( '/admin/2fa',                 'Admin\AuthController@show2FA',              ['admin.auth']);
$router->post('/admin/2fa',                 'Admin\AuthController@verify2FA',            ['admin.auth', 'csrf']);

// =============================================================================
// DASHBOARD
// =============================================================================

$router->get('/admin',                      'Admin\DashboardController@index');
$router->get('/admin/dashboard',            'Admin\DashboardController@index',           ['admin.auth']);
$router->get('/admin/dashboard/stats',      'Admin\DashboardController@stats',           ['admin.auth']);

// =============================================================================
// EVENTS
// =============================================================================

$router->get(   '/admin/events',            'Admin\EventController@index',               ['admin.auth']);
$router->get(   '/admin/events/create',     'Admin\EventController@create',              ['admin.auth', 'admin.role:admin']);
$router->post(  '/admin/events',            'Admin\EventController@store',               ['admin.auth', 'admin.role:admin', 'csrf']);
$router->get(   '/admin/events/{id}',       'Admin\EventController@show',                ['admin.auth']);
$router->get(   '/admin/events/{id}/edit',  'Admin\EventController@edit',                ['admin.auth', 'admin.role:admin']);
$router->put(   '/admin/events/{id}',       'Admin\EventController@update',              ['admin.auth', 'admin.role:admin', 'csrf']);
$router->delete('/admin/events/{id}',       'Admin\EventController@destroy',             ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->put(   '/admin/events/{id}/status','Admin\EventController@updateStatus',        ['admin.auth', 'admin.role:admin', 'csrf']);
$router->post(  '/admin/events/{id}/clone', 'Admin\EventController@clone',               ['admin.auth', 'admin.role:admin', 'csrf']);

// =============================================================================
// LANDING PAGES
// =============================================================================

$router->get( '/admin/events/{id}/landing',        'Admin\LandingPageController@edit',  ['admin.auth']);
$router->put( '/admin/events/{id}/landing',        'Admin\LandingPageController@update',['admin.auth', 'csrf']);
$router->put( '/admin/events/{id}/landing/publish','Admin\LandingPageController@publish',['admin.auth', 'csrf']);

// =============================================================================
// SPEAKERS
// =============================================================================

$router->get(   '/admin/speakers',          'Admin\SpeakerController@index',             ['admin.auth']);
$router->get(   '/admin/speakers/create',   'Admin\SpeakerController@create',            ['admin.auth']);
$router->post(  '/admin/speakers',          'Admin\SpeakerController@store',             ['admin.auth', 'csrf']);
$router->get(   '/admin/speakers/{id}/edit','Admin\SpeakerController@edit',              ['admin.auth']);
$router->put(   '/admin/speakers/{id}',     'Admin\SpeakerController@update',            ['admin.auth', 'csrf']);
$router->delete('/admin/speakers/{id}',     'Admin\SpeakerController@destroy',           ['admin.auth', 'admin.role:admin', 'csrf']);

// --- Assign speakers to event ------------------------------------------------
$router->get(  '/admin/events/{id}/speakers',       'Admin\EventSpeakerController@index',  ['admin.auth']);
$router->post( '/admin/events/{id}/speakers',       'Admin\EventSpeakerController@attach', ['admin.auth', 'csrf']);
$router->put(  '/admin/events/{id}/speakers/{sid}', 'Admin\EventSpeakerController@update', ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/speakers/{sid}','Admin\EventSpeakerController@detach', ['admin.auth', 'csrf']);

// =============================================================================
// SPONSORS
// =============================================================================

$router->get(   '/admin/events/{id}/sponsors',       'Admin\SponsorController@index',   ['admin.auth']);
$router->post(  '/admin/events/{id}/sponsors',       'Admin\SponsorController@store',   ['admin.auth', 'csrf']);
$router->put(   '/admin/events/{id}/sponsors/{sid}', 'Admin\SponsorController@update',  ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/sponsors/{sid}', 'Admin\SponsorController@destroy', ['admin.auth', 'csrf']);

// =============================================================================
// REGISTRATION
// =============================================================================

// --- Form Builder ------------------------------------------------------------
$router->get( '/admin/events/{id}/registration/form',         'Admin\RegistrationFormController@edit',         ['admin.auth']);
$router->put( '/admin/events/{id}/registration/form',         'Admin\RegistrationFormController@update',       ['admin.auth', 'csrf']);
$router->post('/admin/events/{id}/registration/form/fields',  'Admin\RegistrationFormController@addField',     ['admin.auth', 'csrf']);
$router->put( '/admin/events/{id}/registration/form/fields/{fid}', 'Admin\RegistrationFormController@updateField', ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/registration/form/fields/{fid}','Admin\RegistrationFormController@deleteField',['admin.auth', 'csrf']);

// --- Registrant Management ---------------------------------------------------
$router->get(  '/admin/events/{id}/registrations',            'Admin\RegistrationController@index',            ['admin.auth']);
$router->get(  '/admin/events/{id}/registrations/export',     'Admin\RegistrationController@export',           ['admin.auth']);
$router->get(  '/admin/events/{id}/registrations/{rid}',      'Admin\RegistrationController@show',             ['admin.auth']);
$router->put(  '/admin/events/{id}/registrations/{rid}/approve','Admin\RegistrationController@approve',        ['admin.auth', 'csrf']);
$router->put(  '/admin/events/{id}/registrations/{rid}/reject','Admin\RegistrationController@reject',          ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/registrations/{rid}',     'Admin\RegistrationController@destroy',          ['admin.auth', 'admin.role:admin', 'csrf']);
$router->post( '/admin/events/{id}/registrations/bulk-approve','Admin\RegistrationController@bulkApprove',     ['admin.auth', 'csrf']);

// =============================================================================
// WEBCAST
// =============================================================================

$router->get( '/admin/events/{id}/webcast',            'Admin\WebcastController@show',         ['admin.auth']);
$router->get( '/admin/events/{id}/webcast/setup',      'Admin\WebcastController@setup',        ['admin.auth']);
$router->put( '/admin/events/{id}/webcast',            'Admin\WebcastController@update',       ['admin.auth', 'csrf']);
$router->post('/admin/events/{id}/webcast/go-live',    'Admin\WebcastController@goLive',       ['admin.auth', 'admin.role:admin', 'csrf']);
$router->post('/admin/events/{id}/webcast/pause',      'Admin\WebcastController@pause',        ['admin.auth', 'csrf']);
$router->post('/admin/events/{id}/webcast/end',        'Admin\WebcastController@end',          ['admin.auth', 'admin.role:admin', 'csrf']);
$router->get( '/admin/events/{id}/webcast/backstage',  'Admin\WebcastController@backstage',    ['admin.auth']);

// --- Agenda ------------------------------------------------------------------
$router->post(  '/admin/events/{id}/webcast/agenda',         'Admin\AgendaController@store',   ['admin.auth', 'csrf']);
$router->put(   '/admin/events/{id}/webcast/agenda/{aid}',   'Admin\AgendaController@update',  ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/webcast/agenda/{aid}',   'Admin\AgendaController@destroy', ['admin.auth', 'csrf']);
$router->post(  '/admin/events/{id}/webcast/agenda/reorder', 'Admin\AgendaController@reorder', ['admin.auth', 'csrf']);

// =============================================================================
// QUESTIONS (Q&A MODERATION)
// =============================================================================

$router->get(  '/admin/events/{id}/questions',            'Admin\QuestionController@index',    ['admin.auth']);
$router->put(  '/admin/events/{id}/questions/{qid}/approve','Admin\QuestionController@approve',['admin.auth', 'csrf']);
$router->put(  '/admin/events/{id}/questions/{qid}/dismiss','Admin\QuestionController@dismiss',['admin.auth', 'csrf']);
$router->put(  '/admin/events/{id}/questions/{qid}/answer', 'Admin\QuestionController@answer', ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/questions/{qid}',       'Admin\QuestionController@destroy',['admin.auth', 'csrf']);
$router->get(  '/admin/events/{id}/questions/export',       'Admin\QuestionController@export', ['admin.auth']);

// =============================================================================
// POLLS
// =============================================================================

$router->get(   '/admin/events/{id}/polls',              'Admin\PollController@index',         ['admin.auth']);
$router->post(  '/admin/events/{id}/polls',              'Admin\PollController@store',         ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/polls/{pid}/edit',   'Admin\PollController@edit',          ['admin.auth']);
$router->put(   '/admin/events/{id}/polls/{pid}',        'Admin\PollController@update',        ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/polls/{pid}',        'Admin\PollController@destroy',       ['admin.auth', 'csrf']);
$router->post(  '/admin/events/{id}/polls/{pid}/launch', 'Admin\PollController@launch',        ['admin.auth', 'csrf']);
$router->post(  '/admin/events/{id}/polls/{pid}/close',  'Admin\PollController@close',         ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/polls/{pid}/results','Admin\PollController@results',       ['admin.auth']);

// =============================================================================
// QUIZ
// =============================================================================

$router->get(   '/admin/events/{id}/quizzes',                    'Admin\QuizController@index',          ['admin.auth']);
$router->get(   '/admin/events/{id}/quizzes/create',             'Admin\QuizController@create',         ['admin.auth']);
$router->post(  '/admin/events/{id}/quizzes',                    'Admin\QuizController@store',          ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/quizzes/{qid}/edit',         'Admin\QuizController@edit',           ['admin.auth']);
$router->put(   '/admin/events/{id}/quizzes/{qid}',              'Admin\QuizController@update',         ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/quizzes/{qid}',              'Admin\QuizController@destroy',        ['admin.auth', 'csrf']);
$router->put(   '/admin/events/{id}/quizzes/{qid}/publish',      'Admin\QuizController@publish',        ['admin.auth', 'csrf']);

// --- Quiz Questions ----------------------------------------------------------
$router->post(  '/admin/quizzes/{qid}/questions',                'Admin\QuizQuestionController@store',  ['admin.auth', 'csrf']);
$router->put(   '/admin/quizzes/{qid}/questions/{qqid}',         'Admin\QuizQuestionController@update', ['admin.auth', 'csrf']);
$router->delete('/admin/quizzes/{qid}/questions/{qqid}',         'Admin\QuizQuestionController@destroy',['admin.auth', 'csrf']);
$router->post(  '/admin/quizzes/{qid}/questions/reorder',        'Admin\QuizQuestionController@reorder',['admin.auth', 'csrf']);

// --- Quiz Results ------------------------------------------------------------
$router->get(   '/admin/events/{id}/quizzes/{qid}/results',      'Admin\QuizController@results',        ['admin.auth']);
$router->get(   '/admin/events/{id}/quizzes/{qid}/results/export','Admin\QuizController@exportResults', ['admin.auth']);

// =============================================================================
// SURVEYS
// =============================================================================

$router->get(   '/admin/events/{id}/surveys',                   'Admin\SurveyController@index',          ['admin.auth']);
$router->get(   '/admin/events/{id}/surveys/create',            'Admin\SurveyController@create',         ['admin.auth']);
$router->post(  '/admin/events/{id}/surveys',                   'Admin\SurveyController@store',          ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/surveys/{sid}/edit',        'Admin\SurveyController@edit',           ['admin.auth']);
$router->put(   '/admin/events/{id}/surveys/{sid}',             'Admin\SurveyController@update',         ['admin.auth', 'csrf']);
$router->delete('/admin/events/{id}/surveys/{sid}',             'Admin\SurveyController@destroy',        ['admin.auth', 'csrf']);
$router->put(   '/admin/events/{id}/surveys/{sid}/activate',    'Admin\SurveyController@activate',       ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/surveys/{sid}/responses',   'Admin\SurveyController@responses',      ['admin.auth']);
$router->get(   '/admin/events/{id}/surveys/{sid}/responses/export','Admin\SurveyController@export',     ['admin.auth']);

// =============================================================================
// FEEDBACK
// =============================================================================

$router->get('/admin/events/{id}/feedback',          'Admin\FeedbackController@index',   ['admin.auth']);
$router->get('/admin/events/{id}/feedback/export',   'Admin\FeedbackController@export',  ['admin.auth']);
$router->put('/admin/events/{id}/feedback/{fid}/flag','Admin\FeedbackController@flag',   ['admin.auth', 'csrf']);
$router->put('/admin/events/{id}/feedback/{fid}/hide','Admin\FeedbackController@hide',   ['admin.auth', 'csrf']);

// =============================================================================
// ATTENDANCE
// =============================================================================

$router->get('/admin/events/{id}/attendance',        'Admin\AttendanceController@index',  ['admin.auth']);
$router->get('/admin/events/{id}/attendance/live',   'Admin\AttendanceController@live',   ['admin.auth']);
$router->get('/admin/events/{id}/attendance/export', 'Admin\AttendanceController@export', ['admin.auth']);

// =============================================================================
// EMAIL INVITATIONS
// =============================================================================

$router->get(   '/admin/events/{id}/emails',                   'Admin\EmailCampaignController@index',    ['admin.auth']);
$router->get(   '/admin/events/{id}/emails/create',            'Admin\EmailCampaignController@create',   ['admin.auth']);
$router->post(  '/admin/events/{id}/emails',                   'Admin\EmailCampaignController@store',    ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/emails/{cid}/edit',        'Admin\EmailCampaignController@edit',     ['admin.auth']);
$router->put(   '/admin/events/{id}/emails/{cid}',             'Admin\EmailCampaignController@update',   ['admin.auth', 'csrf']);
$router->post(  '/admin/events/{id}/emails/{cid}/send',        'Admin\EmailCampaignController@send',     ['admin.auth', 'admin.role:admin', 'csrf']);
$router->post(  '/admin/events/{id}/emails/{cid}/schedule',    'Admin\EmailCampaignController@schedule', ['admin.auth', 'csrf']);
$router->post(  '/admin/events/{id}/emails/{cid}/cancel',      'Admin\EmailCampaignController@cancel',   ['admin.auth', 'csrf']);
$router->get(   '/admin/events/{id}/emails/{cid}/logs',        'Admin\EmailCampaignController@logs',     ['admin.auth']);
$router->delete('/admin/events/{id}/emails/{cid}',             'Admin\EmailCampaignController@destroy',  ['admin.auth', 'csrf']);

// --- Email Templates ---------------------------------------------------------
$router->get(   '/admin/email-templates',             'Admin\EmailTemplateController@index',   ['admin.auth']);
$router->get(   '/admin/email-templates/create',      'Admin\EmailTemplateController@create',  ['admin.auth']);
$router->post(  '/admin/email-templates',             'Admin\EmailTemplateController@store',   ['admin.auth', 'csrf']);
$router->get(   '/admin/email-templates/{id}/edit',   'Admin\EmailTemplateController@edit',    ['admin.auth']);
$router->put(   '/admin/email-templates/{id}',        'Admin\EmailTemplateController@update',  ['admin.auth', 'csrf']);
$router->delete('/admin/email-templates/{id}',        'Admin\EmailTemplateController@destroy', ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->post(  '/admin/email-templates/{id}/preview','Admin\EmailTemplateController@preview', ['admin.auth', 'csrf']);

// =============================================================================
// REPORTS
// =============================================================================

$router->get( '/admin/reports',                       'Admin\ReportController@index',      ['admin.auth']);
$router->post('/admin/reports/generate',              'Admin\ReportController@generate',   ['admin.auth', 'csrf']);
$router->get( '/admin/reports/{rid}',                 'Admin\ReportController@show',       ['admin.auth']);
$router->get( '/admin/reports/{rid}/download',        'Admin\ReportController@download',   ['admin.auth']);
$router->delete('/admin/reports/{rid}',               'Admin\ReportController@destroy',    ['admin.auth', 'csrf']);

// --- Scoped report shortcuts (event-specific) --------------------------------
$router->get('/admin/events/{id}/reports',            'Admin\ReportController@eventIndex', ['admin.auth']);
$router->post('/admin/events/{id}/reports/generate',  'Admin\ReportController@generate',  ['admin.auth', 'csrf']);

// =============================================================================
// ACTIVITY LOGS
// =============================================================================

$router->get('/admin/activity-logs',                  'Admin\ActivityLogController@index',        ['admin.auth', 'admin.role:super_admin']);
$router->get('/admin/activity-logs/export',           'Admin\ActivityLogController@export',       ['admin.auth', 'admin.role:super_admin']);
$router->get('/admin/events/{id}/activity-logs',      'Admin\ActivityLogController@eventIndex',   ['admin.auth']);

// =============================================================================
// USERS (ATTENDEE MANAGEMENT)
// =============================================================================

$router->get(   '/admin/users',                       'Admin\UserController@index',        ['admin.auth']);
$router->get(   '/admin/users/{uid}',                 'Admin\UserController@show',         ['admin.auth']);
$router->put(   '/admin/users/{uid}/status',          'Admin\UserController@updateStatus', ['admin.auth', 'admin.role:admin', 'csrf']);
$router->delete('/admin/users/{uid}',                 'Admin\UserController@destroy',      ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->get(   '/admin/users/export',                'Admin\UserController@export',       ['admin.auth', 'admin.role:admin']);
$router->post(  '/admin/users/{uid}/impersonate',     'Admin\UserController@impersonate',  ['admin.auth', 'admin.role:super_admin', 'csrf']);

// =============================================================================
// ADMIN MANAGEMENT (SUPER ADMIN ONLY)
// =============================================================================

$router->get(   '/admin/admins',                      'Admin\AdminManagerController@index',  ['admin.auth', 'admin.role:super_admin']);
$router->get(   '/admin/admins/create',               'Admin\AdminManagerController@create', ['admin.auth', 'admin.role:super_admin']);
$router->post(  '/admin/admins',                      'Admin\AdminManagerController@store',  ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->get(   '/admin/admins/{aid}/edit',           'Admin\AdminManagerController@edit',   ['admin.auth', 'admin.role:super_admin']);
$router->put(   '/admin/admins/{aid}',                'Admin\AdminManagerController@update', ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->delete('/admin/admins/{aid}',                'Admin\AdminManagerController@destroy',['admin.auth', 'admin.role:super_admin', 'csrf']);

// =============================================================================
// BRANDING
// =============================================================================

$router->get(   '/admin/brands',                      'Admin\BrandController@index',       ['admin.auth', 'admin.role:admin']);
$router->get(   '/admin/brands/create',               'Admin\BrandController@create',      ['admin.auth', 'admin.role:admin']);
$router->post(  '/admin/brands',                      'Admin\BrandController@store',       ['admin.auth', 'admin.role:admin', 'csrf']);
$router->get(   '/admin/brands/{bid}/edit',           'Admin\BrandController@edit',        ['admin.auth', 'admin.role:admin']);
$router->put(   '/admin/brands/{bid}',                'Admin\BrandController@update',      ['admin.auth', 'admin.role:admin', 'csrf']);
$router->delete('/admin/brands/{bid}',                'Admin\BrandController@destroy',     ['admin.auth', 'admin.role:super_admin', 'csrf']);

// =============================================================================
// SETTINGS
// =============================================================================

$router->get('/admin/settings',                       'Admin\SettingController@index',            ['admin.auth', 'admin.role:super_admin']);
$router->put('/admin/settings',                       'Admin\SettingController@update',           ['admin.auth', 'admin.role:super_admin', 'csrf']);
$router->get('/admin/events/{id}/settings',           'Admin\SettingController@eventSettings',    ['admin.auth', 'admin.role:admin']);
$router->put('/admin/events/{id}/settings',           'Admin\SettingController@updateEvent',      ['admin.auth', 'admin.role:admin', 'csrf']);
