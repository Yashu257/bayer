<?php

/**
 * API ROUTES — Stateless JSON endpoints
 *
 * All routes prefixed with /api/v1
 * Responses: JSON only (Content-Type: application/json)
 *
 * Middleware keys:
 *   api.auth    — validate Bearer token from user_sessions
 *   api.admin   — validate Bearer token from admin_sessions
 *   throttle    — rate limit per IP / token
 *   csrf        — NOT required for API (token-based auth replaces it)
 */

// =============================================================================
// PUBLIC API — NO AUTH
// =============================================================================

// --- Health check ------------------------------------------------------------
$router->get('/api/v1/health',                        'Api\SystemController@health');
$router->get('/api/v1/version',                       'Api\SystemController@version');

// --- Events (public discovery) -----------------------------------------------
$router->get('/api/v1/events',                        'Api\EventController@index',         ['throttle:60,1']);
$router->get('/api/v1/events/{slug}',                 'Api\EventController@show',          ['throttle:60,1']);

// --- Selfie upload (public for preview) --------------------------------------
$router->post('/api/v1/selfies/upload',               'Api\SelfieController@upload',       ['throttle:60,1']);

// =============================================================================
// ATTENDEE API — AUTH REQUIRED
// =============================================================================

// --- Auth (token issue / revoke) ---------------------------------------------
$router->post('/api/v1/auth/login',                   'Api\AuthController@login',          ['throttle:5,1']);
$router->post('/api/v1/auth/logout',                  'Api\AuthController@logout',         ['api.auth']);
$router->post('/api/v1/auth/refresh',                 'Api\AuthController@refresh',        ['api.auth']);
$router->get( '/api/v1/auth/me',                      'Api\AuthController@me',             ['api.auth']);

// --- Webcast ------------------------------------------------------------------
$router->get( '/api/v1/webcasts/{id}',                'Api\WebcastController@show',        ['api.auth']);
$router->get( '/api/v1/webcasts/{id}/stream-token',   'Api\WebcastController@streamToken', ['api.auth', 'throttle:10,1']);
$router->post('/api/v1/webcasts/{id}/heartbeat',      'Api\WebcastController@heartbeat',   ['api.auth', 'throttle:30,1']);

// --- Questions ---------------------------------------------------------------
$router->get( '/api/v1/webcasts/{id}/questions',      'Api\QuestionController@index',      ['api.auth']);
$router->post('/api/v1/webcasts/{id}/questions',      'Api\QuestionController@store',      ['api.auth', 'throttle:10,1']);
$router->post('/api/v1/questions/{qid}/upvote',       'Api\QuestionController@upvote',     ['api.auth', 'throttle:20,1']);

// --- Polls -------------------------------------------------------------------
$router->get( '/api/v1/webcasts/{id}/polls/active',   'Api\PollController@active',         ['api.auth']);
$router->post('/api/v1/polls/{pid}/vote',             'Api\PollController@vote',           ['api.auth', 'throttle:10,1']);
$router->get( '/api/v1/polls/{pid}/results',          'Api\PollController@results',        ['api.auth']);

// --- Quiz --------------------------------------------------------------------
$router->get( '/api/v1/quizzes/{qid}',                'Api\QuizController@show',           ['api.auth']);
$router->post('/api/v1/quizzes/{qid}/attempts',       'Api\QuizController@startAttempt',   ['api.auth']);
$router->post('/api/v1/quiz-attempts/{aid}/answers',  'Api\QuizController@submitAnswer',   ['api.auth']);
$router->post('/api/v1/quiz-attempts/{aid}/submit',   'Api\QuizController@submitAttempt',  ['api.auth']);
$router->get( '/api/v1/quiz-attempts/{aid}/result',   'Api\QuizController@result',         ['api.auth']);

// --- Survey ------------------------------------------------------------------
$router->get( '/api/v1/surveys/{sid}',                'Api\SurveyController@show',         ['api.auth']);
$router->post('/api/v1/surveys/{sid}/responses',      'Api\SurveyController@submit',       ['api.auth', 'throttle:5,5']);

// --- Feedback ----------------------------------------------------------------
$router->post('/api/v1/events/{eid}/feedback',        'Api\FeedbackController@store',      ['api.auth', 'throttle:2,60']);

// =============================================================================
// ADMIN API — ADMIN TOKEN REQUIRED
// =============================================================================

// --- Auth --------------------------------------------------------------------
$router->post('/api/v1/admin/auth/login',             'Api\Admin\AuthController@login',    ['throttle:5,1']);
$router->post('/api/v1/admin/auth/logout',            'Api\Admin\AuthController@logout',   ['api.admin']);

// --- Dashboard ---------------------------------------------------------------
$router->get( '/api/v1/admin/dashboard/stats',        'Api\Admin\DashboardController@stats',      ['api.admin']);
$router->get( '/api/v1/admin/dashboard/activity',     'Api\Admin\DashboardController@activity',   ['api.admin']);

// --- Live Webcast Controls ---------------------------------------------------
$router->get( '/api/v1/admin/webcasts/{id}/live-stats','Api\Admin\WebcastController@liveStats',   ['api.admin']);
$router->post('/api/v1/admin/webcasts/{id}/go-live',  'Api\Admin\WebcastController@goLive',       ['api.admin']);
$router->post('/api/v1/admin/webcasts/{id}/end',      'Api\Admin\WebcastController@end',          ['api.admin']);

// --- Live Q&A Moderation  ----------------------------------------------------
$router->get( '/api/v1/admin/webcasts/{id}/questions',         'Api\Admin\QuestionController@index',   ['api.admin']);
$router->put( '/api/v1/admin/questions/{qid}/approve',         'Api\Admin\QuestionController@approve', ['api.admin']);
$router->put( '/api/v1/admin/questions/{qid}/dismiss',         'Api\Admin\QuestionController@dismiss', ['api.admin']);

// --- Live Poll Controls ------------------------------------------------------
$router->post('/api/v1/admin/polls/{pid}/launch',     'Api\Admin\PollController@launch',   ['api.admin']);
$router->post('/api/v1/admin/polls/{pid}/close',      'Api\Admin\PollController@close',    ['api.admin']);
$router->get( '/api/v1/admin/polls/{pid}/results',    'Api\Admin\PollController@results',  ['api.admin']);

// --- Live Attendance ---------------------------------------------------------
$router->get( '/api/v1/admin/webcasts/{id}/attendance/live',   'Api\Admin\AttendanceController@live',  ['api.admin']);

// --- Report generation (async trigger) ---------------------------------------
$router->post('/api/v1/admin/reports/generate',       'Api\Admin\ReportController@generate',   ['api.admin']);
$router->get( '/api/v1/admin/reports/{rid}/status',   'Api\Admin\ReportController@status',     ['api.admin']);
