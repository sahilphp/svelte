<?php

declare(strict_types=1);

namespace App\Http;

use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Response;

/**
 * JSON health endpoint that proves boot, configuration and routing work
 * without rendering a Svelte page.
 */
final class HealthController
{
    public function __construct(private readonly ApplicationContract $application)
    {
    }

    public function response(): Response
    {
        return Response::json([
            'status' => 'ok',
            'framework' => (string) $this->application->config()->get('app.framework', 'SahilPHP'),
            'environment' => (string) $this->application->config()->get('app.environment', 'development'),
            'booted' => $this->application->isBooted(),
            'php' => PHP_VERSION,
            'timestamp' => gmdate(DATE_ATOM),
        ]);
    }
}
