<?php

declare(strict_types=1);

namespace App\Frontend;

use Sahil\Http\Response;

/**
 * Convenience facade used by controllers.
 *
 * The framework-independent pieces (configuration, page resolution, asset
 * resolution and the renderer) are regular container services. This facade
 * only resolves the renderer from the application container, which keeps
 * controller code as small as the advertised API:
 *
 *     return Svelte::render('pages.home', $data);
 *
 * Unit tests can exercise {@see SvelteRenderer} directly without the facade
 * or a booted application.
 */
final class Svelte
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $page, array $data = [], int $status = 200): Response
    {
        /** @var SvelteRenderer $renderer */
        $renderer = app()->container()->make(SvelteRenderer::class);

        return $renderer->render($page, $data, $status);
    }
}
