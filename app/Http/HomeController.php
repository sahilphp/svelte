<?php

declare(strict_types=1);

namespace App\Http;

use App\Frontend\Svelte;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Response;

/**
 * The starter-kit landing page.
 *
 * Everything the page displays comes from PHP configuration and the runtime
 * environment; the Svelte component receives it as props through the JSON
 * payload in the rendered document.
 */
final class HomeController
{
    public function __construct(private readonly ApplicationContract $application)
    {
    }

    public function response(): Response
    {
        $config = $this->application->config();
        $development = $config->get('frontend.development', true) === true;

        return Svelte::render('pages.home', [
            'title' => 'SahilPHP + Svelte',
            'app_name' => (string) $config->get('app.name', 'SahilPHP'),
            'php_version' => PHP_VERSION,
            'environment' => (string) $config->get('app.environment', 'development'),
            'framework_version' => (string) $config->get('app.framework', 'SahilPHP'),
            'frontend' => 'Svelte 5 + TypeScript + Vite',
            'asset_mode' => $development ? 'Vite dev server' : 'Production build',
            'rendered_at' => gmdate(DATE_ATOM),
            'features' => [
                [
                    'title' => 'Controller data as props',
                    'description' => 'PHP arrays are serialized into a JSON payload and mounted as Svelte props, with no unsafe string interpolation.',
                ],
                [
                    'title' => 'Predictable page resolution',
                    'description' => 'pages.home resolves Pages/Home.svelte; nested names such as pages.dashboard.index resolve nested components.',
                ],
                [
                    'title' => 'Vite in both modes',
                    'description' => 'Development loads the Vite client and HMR entry; production reads the manifest and verifies every built asset.',
                ],
                [
                    'title' => 'Ordinary HTTP responses',
                    'description' => 'Svelte::render() returns a normal SahilPHP Response, so status codes and headers keep working exactly as usual.',
                ],
            ],
        ]);
    }
}
