<?php

/**
 * WEB ROUTES — Public & Authenticated Attendee Routes
 *
 * Format: $router->METHOD('uri', 'Controller@method', [middleware]);
 *
 * Middleware keys:
 *   guest       — redirect to dashboard if already logged in
 *   auth        — require authenticated user session
 *   verified    — require email verification
 *   registered  — require active event registration
 *   throttle    — rate limiter (login, forms)
 *   csrf        — CSRF token validation (all POST/PUT/DELETE)
 *   event       — resolve event by slug, bind to request
 */

// =============================================================================
// PUBLIC — NO AUTH REQUIRED
// =============================================================================

// --- Home / Platform Index ---------------------------------------------------
$router->get('/',                           'Frontend\HomeController@index');
$router->get('/events',                     'Frontend\HomeController@events');

// --- Event Landing Page ------------------------------------------------------
$router->get('/e/{slug}',                   'Frontend\LandingPageController@show',       ['event']);
$router->get('/e/{slug}/speakers',          'Frontend\LandingPageController@speakers',   ['event']);
$router->get('/e/{slug}/agenda',            'Frontend\LandingPageController@agenda',     ['event']);
$router->get('/e/{slug}/sponsors',          'Frontend\LandingPageController@sponsors',   ['event']);

// =============================================================================
// EVENT REGISTRATION — PUBLIC (no user account required)
// =============================================================================

$router->get( '/e/{slug}/register',         'Frontend\RegistrationController@showForm',  ['event']);
$router->post('/e/{slug}/register',         'Frontend\RegistrationController@submit',    ['event', 'csrf', 'throttle:5,1']);
$router->get( '/e/{slug}/register/confirm', 'Frontend\RegistrationController@confirm',   ['event']);
$router->get( '/e/{slug}/register/pending', 'Frontend\RegistrationController@pending',   ['event']);

// =============================================================================
// WEBCAST — AUTHENTICATED ATTENDEE (registered for the event)
// =============================================================================

// --- Webcast Room ------------------------------------------------------------
$router->get( '/e/{slug}/watch',            'Frontend\WebcastController@room',           ['auth', 'verified', 'event', 'registered']);
$router->post('/e/{slug}/watch/heartbeat',  'Frontend\WebcastController->heartbeat',      ['auth', 'event', 'registered', 'csrf']);

// --- Q&A ---------------------------------------------------------------------
$router->get( '/e/{slug}/questions',        'Frontend\QuestionController@index',         ['auth', 'event', 'registered']);
$router->post('/e/{slug}/questions',        'Frontend\QuestionController@submit',        ['auth', 'event', 'registered', 'csrf', 'throttle:10,1']);
$router->post('/e/{slug}/questions/{id}/upvote', 'Frontend\QuestionController@upvote',   ['auth', 'event', 'registered', 'csrf']);

// --- Polls -------------------------------------------------------------------
$router->get( '/e/{slug}/polls/active',     'Frontend\PollController@active',            ['auth', 'event', 'registered']);
$router->post('/e/{slug}/polls/{id}/vote',  'Frontend\PollController@vote',              ['auth', 'event', 'registered', 'csrf']);
$router->get( '/e/{slug}/polls/{id}/results', 'Frontend\PollController@results',         ['auth', 'event', 'registered']);

// --- Quiz --------------------------------------------------------------------
$router->get( '/e/{slug}/quiz/{id}',        'Frontend\QuizController@show',              ['auth', 'verified', 'event', 'registered']);
$router->post('/e/{slug}/quiz/{id}/start',  'Frontend\QuizController@start',             ['auth', 'event', 'registered', 'csrf']);
$router->post('/e/{slug}/quiz/{id}/answer', 'Frontend\QuizController@answer',            ['auth', 'event', 'registered', 'csrf']);
$router->post('/e/{slug}/quiz/{id}/submit', 'Frontend\QuizController@submit',            ['auth', 'event', 'registered', 'csrf']);
$router->get( '/e/{slug}/quiz/{id}/result', 'Frontend\QuizController@result',            ['auth', 'event', 'registered']);

// --- Survey ------------------------------------------------------------------
$router->get( '/e/{slug}/survey/{id}',         'Frontend\SurveyController@show',         ['auth', 'event', 'registered']);
$router->post('/e/{slug}/survey/{id}/submit',  'Frontend\SurveyController@submit',       ['auth', 'event', 'registered', 'csrf']);
$router->get( '/e/{slug}/survey/{id}/thanks',  'Frontend\SurveyController@thankyou',     ['auth', 'event']);

// --- Feedback ----------------------------------------------------------------
$router->get( '/e/{slug}/feedback',         'Frontend\FeedbackController@show',          ['auth', 'event', 'registered']);
$router->post('/e/{slug}/feedback',         'Frontend\FeedbackController@submit',        ['auth', 'event', 'registered', 'csrf', 'throttle:2,5']);

// =============================================================================
// USER PROFILE — AUTHENTICATED
// =============================================================================

$router->get( '/profile',                   'Frontend\ProfileController@show',           ['auth', 'verified']);
$router->get( '/profile/edit',              'Frontend\ProfileController@edit',           ['auth', 'verified']);
$router->put( '/profile',                   'Frontend\ProfileController@update',         ['auth', 'verified', 'csrf']);
$router->put( '/profile/password',          'Frontend\ProfileController@updatePassword', ['auth', 'verified', 'csrf']);
$router->get( '/profile/events',            'Frontend\ProfileController@myEvents',       ['auth', 'verified']);
$router->get( '/profile/certificates/{id}', 'Frontend\ProfileController@certificate',   ['auth', 'verified']);

// =============================================================================
// ERROR PAGES
// =============================================================================

$router->get('/403',                        'Frontend\ErrorController@forbidden');
$router->get('/404',                        'Frontend\ErrorController@notFound');
$router->get('/500',                        'Frontend\ErrorController@serverError');
$router->get('/maintenance',                'Frontend\ErrorController@maintenance');
