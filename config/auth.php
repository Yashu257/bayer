<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User Auth
    |--------------------------------------------------------------------------
    */
    'user' => [
        'session_key'       => 'auth_user',
        'session_lifetime'  => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),   // minutes
        'remember_lifetime' => 43200,   // 30 days in minutes
        'cookie_name'       => 'remember_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Auth
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'session_key'      => 'auth_admin',
        'session_lifetime' => 60,       // minutes — stricter for admin
        'cookie_name'      => 'remember_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length'          => 8,
        'require_uppercase'   => true,
        'require_number'      => true,
        'require_special'     => true,
        'reset_token_expires' => 60,    // minutes
        'algo'                => PASSWORD_ARGON2ID,
        'algo_options'        => [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token lengths (bytes, then hex-encoded → ×2 chars)
    |--------------------------------------------------------------------------
    */
    'token' => [
        'verification' => 32,
        'reset'        => 32,
        'remember'     => 40,
        'api'          => 64,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limiting for auth endpoints
    |--------------------------------------------------------------------------
    */
    'throttle' => [
        'login'          => ['max' => 5,  'decay_minutes' => 1],
        'register'       => ['max' => 3,  'decay_minutes' => 1],
        'password_reset' => ['max' => 3,  'decay_minutes' => 10],
    ],
];
