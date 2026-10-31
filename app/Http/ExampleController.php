<?php

declare(strict_types=1);

namespace App\Http;

use App\Frontend\Svelte;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * Demonstrates structured PHP data reaching a Svelte page.
 *
 * The page receives nested objects, lists and request metadata. The payload
 * is JSON-encoded by the renderer, so the component can iterate and inspect
 * it without server-side markup generation.
 */
final class ExampleController
{
    public function __construct(private readonly ApplicationContract $application)
    {
    }

    public function response(Request $request): Response
    {
        return Svelte::render('pages.example', [
            'title' => 'PHP data in Svelte',
            'app_name' => (string) $this->application->config()->get('app.name', 'SahilPHP'),
            'php_version' => PHP_VERSION,
            'profile' => [
                'name' => 'Ada Lovelace',
                'role' => 'Senior software engineer',
                'location' => 'Remote',
                'summary' => 'A small profile object rendered by Svelte from data prepared in a PHP controller.',
                'skills' => ['PHP 8', 'Svelte 5', 'TypeScript', 'Vite', 'HTTP'],
            ],
            'stats' => [
                ['label' => 'Projects', 'value' => '12'],
                ['label' => 'Contributions', 'value' => '248'],
                ['label' => 'Reviews', 'value' => '37'],
            ],
            'request' => [
                'method' => $request->method(),
                'path' => $request->path(),
                'query' => $request->query(),
                'server_time' => gmdate(DATE_ATOM),
            ],
        ]);
    }
}
