<?php

declare(strict_types=1);

namespace App\Frontend;

use Sahil\Contracts\Rendering\RenderAdapterContract;
use Sahil\Http\Response;

/**
 * Svelte render environment for the unified rendering API — delegates to
 * the kit's {@see SvelteRenderer}; core owns routing of page + data.
 */
final class SvelteAdapter implements RenderAdapterContract
{
    public function __construct(
        private readonly SvelteRenderer $renderer,
    ) {
    }

    public function environment(): string
    {
        return 'svelte';
    }

    public function render(string $page, array $data, int $status): Response
    {
        return $this->renderer->render($page, $data, $status);
    }
}
