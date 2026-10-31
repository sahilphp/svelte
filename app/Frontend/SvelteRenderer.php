<?php

declare(strict_types=1);

namespace App\Frontend;

use Sahil\Http\Response;

/**
 * Turns controller data into a complete Svelte HTML response.
 *
 * This is the one PHP entry point applications use:
 *
 *     return Svelte::render('pages.home', $data);
 *
 * The renderer resolves the page component, serializes data safely, resolves
 * development or production assets and returns an ordinary immutable
 * {@see Response}. Status codes and headers therefore remain the HTTP
 * layer's concern: call `withStatus()`/`withHeader()` on the result when a
 * controller needs them.
 */
final class SvelteRenderer
{
    public function __construct(
        private readonly SvelteConfiguration $configuration,
        private readonly SvelteAssets $assets,
        private readonly string $basePath,
    ) {
    }

    /**
     * @param array<string, mixed> $data props handed to the Svelte component
     */
    public function render(string $page, array $data = [], int $status = 200): Response
    {
        $resolved = $this->page($page);
        $document = new SvelteDocument($this->configuration, $resolved, $data, $this->assets);

        return Response::html($document->html(), $status);
    }

    public function page(string $page): SveltePage
    {
        return SveltePage::fromName(
            $page,
            rtrim($this->basePath, '/').'/'.trim($this->configuration->pagesDirectory(), '/'),
        );
    }

    public function assets(): SvelteAssets
    {
        return $this->assets;
    }

    public function configuration(): SvelteConfiguration
    {
        return $this->configuration;
    }
}
