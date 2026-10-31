<?php

declare(strict_types=1);

namespace App\Providers;

use App\Frontend\SvelteAssets;
use App\Frontend\SvelteConfiguration;
use App\Frontend\SvelteRenderer;
use Sahil\Container\Container;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Foundation\ServiceProvider;

/**
 * Wires the starter-kit-owned Svelte frontend integration.
 *
 * Everything Svelte-specific lives in this starter-kit: the page resolver,
 * payload serializer, Vite asset resolver and renderer. The framework core
 * remains responsible for ordinary HTTP/container concerns.
 */
final class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $container = $this->app()->container();

        $container->singleton(SvelteConfiguration::class, static function (Container $container): SvelteConfiguration {
            $application = $container->make(ApplicationContract::class);

            /** @var array<string, mixed> $configuration */
            $configuration = (array) $application->config()->get('frontend', []);

            return SvelteConfiguration::fromArray($configuration);
        });

        $container->singleton(SvelteAssets::class, static function (Container $container): SvelteAssets {
            $application = $container->make(ApplicationContract::class);

            return new SvelteAssets(
                $container->make(SvelteConfiguration::class),
                $application->basePath(),
            );
        });

        $container->singleton(SvelteRenderer::class, static function (Container $container): SvelteRenderer {
            $application = $container->make(ApplicationContract::class);

            return new SvelteRenderer(
                $container->make(SvelteConfiguration::class),
                $container->make(SvelteAssets::class),
                $application->basePath(),
            );
        });

        $container->alias('svelte', SvelteRenderer::class);
    }
}
