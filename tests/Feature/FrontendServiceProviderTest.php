<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Frontend\Svelte;
use App\Frontend\SvelteAssets;
use App\Frontend\SvelteConfiguration;
use App\Frontend\SvelteRenderer;
use App\Providers\FrontendServiceProvider;
use App\Providers\WebRoutesProvider;
use Sahil\Http\Kernel;
use Sahil\Http\Router;
use Sahil\Testing\ApplicationTestCase;

final class FrontendServiceProviderTest extends ApplicationTestCase
{
    protected function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public function test_frontend_services_are_container_singletons(): void
    {
        $configuration = $this->container()->make(SvelteConfiguration::class);
        $assets = $this->container()->make(SvelteAssets::class);
        $renderer = $this->container()->make(SvelteRenderer::class);

        self::assertSame($configuration, $this->container()->make(SvelteConfiguration::class));
        self::assertSame($assets, $this->container()->make(SvelteAssets::class));
        self::assertSame($renderer, $this->container()->make(SvelteRenderer::class));
        self::assertSame($renderer, $this->container()->make('svelte'));
        self::assertSame($configuration, $renderer->configuration());
        self::assertSame($assets, $renderer->assets());
    }

    public function test_configuration_reflects_config_frontend_php(): void
    {
        $configuration = $this->container()->make(SvelteConfiguration::class);

        self::assertSame('app', $configuration->mountId());
        self::assertSame('title', $configuration->titleKey());
        self::assertSame('en', $configuration->lang());
        self::assertSame('public/build', $configuration->buildPath());
        self::assertSame('build', $configuration->buildPublicPath());
        self::assertSame('.vite/manifest.json', $configuration->manifest());
        self::assertSame('src/main.ts', $configuration->entry());
        self::assertSame('src/pages', $configuration->pagesDirectory());
        self::assertTrue($configuration->verifyAssets());

        // APP_ENV=testing is not production, so development assets are used.
        self::assertTrue($configuration->development());
        self::assertSame('http://127.0.0.1:5173', $configuration->devServer());
    }

    public function test_http_router_and_kernel_are_registered(): void
    {
        self::assertTrue($this->container()->bound(Router::class));
        self::assertTrue($this->container()->bound(Kernel::class));
        self::assertSame($this->container()->make(Router::class), $this->container()->make(Router::class));
        self::assertSame($this->container()->make(Kernel::class), $this->container()->make(Kernel::class));
    }

    public function test_the_svelte_facade_renders_through_the_application_container(): void
    {
        $this->app();

        $response = Svelte::render('pages.home', ['title' => 'Facade']);

        self::assertSame(200, $response->status());
        self::assertStringContainsString('<title>Facade</title>', $response->body());
        self::assertStringContainsString('data-component="Pages/Home.svelte"', $response->body());

        $notFound = Svelte::render('pages.notFound', ['title' => 'Missing'], 404);

        self::assertSame(404, $notFound->status());
    }

    public function test_the_provider_manifest_registers_the_starter_kit_providers(): void
    {
        $providers = $this->providers($this->app());

        self::assertCount(2, $providers);
        self::assertInstanceOf(FrontendServiceProvider::class, $providers[0]);
        self::assertInstanceOf(WebRoutesProvider::class, $providers[1]);
    }
}
