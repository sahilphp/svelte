<?php

declare(strict_types=1);

/**
 * Application configuration.
 *
 * Every value can be overridden through `.env`; no secret belongs here.
 * Values are validated by the framework at boot (environment names, debug
 * type, timezone and log level).
 */
return [
    'name' => env('APP_NAME', 'SahilPHP'),
    'framework' => 'SahilPHP',
    'key' => env('APP_KEY'),
    'environment' => env('APP_ENV', 'development'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'locale' => env('APP_LOCALE', 'en'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'log_level' => env('APP_LOG_LEVEL', 'info'),
];
