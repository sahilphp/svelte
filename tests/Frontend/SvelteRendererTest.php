<?php

declare(strict_types=1);

namespace Tests\Frontend;

use App\Frontend\SvelteDocument;
use App\Frontend\SvelteRenderException;
use App\Frontend\SvelteRenderer;
use PHPUnit\Framework\TestCase;
use Tests\Support\TempProject;

final class SvelteRendererTest extends TestCase
{
    private string $base;
    private SvelteRenderer $renderer;

    protected function setUp(): void
    {
        $this->base = TempProject::create();
        $this->renderer = TempProject::renderer($this->base);
    }

    protected function tearDown(): void
    {
        TempProject::remove($this->base);
    }

    public function test_render_builds_a_complete_html_document(): void
    {
        $response = $this->renderer->render('pages.home', [
            'title' => 'Home',
            'app_name' => 'Demo App',
            'php_version' => PHP_VERSION,
        ]);

        $html = $response->body();

        self::assertSame(200, $response->status());
        self::assertSame('text/html; charset=utf-8', $response->header('Content-Type'));
        self::assertSame((string) strlen($html), $response->header('Content-Length'));
        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('<html lang="en">', $html);
        self::assertStringContainsString('<body data-mount-id="app">', $html);
        self::assertStringContainsString('<title>Home</title>', $html);
        self::assertStringContainsString(
            '<div id="app" data-component="Pages/Home.svelte" data-page="pages.home" data-framework="svelte"></div>',
            $html,
        );
        self::assertStringContainsString('<script type="application/json" id="app-props">', $html);
        self::assertStringContainsString('"app_name":"Demo App"', $html);
        self::assertStringContainsString('"php_version":"'.PHP_VERSION.'"', $html);
        self::assertStringContainsString('http://127.0.0.1:5173/@vite/client', $html);
        self::assertStringContainsString('http://127.0.0.1:5173/src/main.ts', $html);
    }

    public function test_nested_page_names_reach_the_mount_element(): void
    {
        $html = $this->renderer->render('pages.dashboard.index', ['title' => 'Dashboard'])->body();

        self::assertStringContainsString('data-component="Pages/Dashboard/Index.svelte"', $html);
        self::assertStringContainsString('data-page="pages.dashboard.index"', $html);
    }

    public function test_a_custom_mount_id_is_shared_with_the_client_bootstrap(): void
    {
        $renderer = TempProject::renderer($this->base, ['mount_id' => 'svelte-root']);

        $html = $renderer->render('pages.home', ['title' => 'Custom mount'])->body();

        self::assertStringContainsString('<body data-mount-id="svelte-root">', $html);
        self::assertStringContainsString('<div id="svelte-root" ', $html);
        self::assertStringContainsString('<script type="application/json" id="svelte-root-props">', $html);
    }

    public function test_payload_cannot_close_the_script_element(): void
    {
        $html = $this->renderer->render('pages.home', [
            'title' => 'Safe',
            'attack' => '</script><script>alert(1)</script>',
        ])->body();

        self::assertStringNotContainsString('</script><script>alert(1)</script>', $html);
        self::assertStringContainsString('\u003C/script\u003E', $html);
        self::assertSame(1, substr_count($html, '<script type="application/json"'));
    }

    public function test_title_and_language_are_escaped(): void
    {
        $html = $this->renderer->render('pages.home', ['title' => '<b>Injected</b>'])->body();

        self::assertStringContainsString('<title>&lt;b&gt;Injected&lt;/b&gt;</title>', $html);
        self::assertStringNotContainsString('<b>Injected</b>', $html);
    }

    public function test_default_title_is_used_when_none_is_supplied(): void
    {
        $html = $this->renderer->render('pages.home', ['app_name' => 'Demo'])->body();

        self::assertStringContainsString('<title>SahilPHP</title>', $html);
    }

    public function test_nested_title_key_is_supported(): void
    {
        $renderer = TempProject::renderer($this->base, ['title_key' => 'meta.title']);

        $html = $renderer->render('pages.home', ['meta' => ['title' => 'Nested title']])->body();

        self::assertStringContainsString('<title>Nested title</title>', $html);
    }

    public function test_status_and_additional_headers_are_preserved(): void
    {
        $response = $this->renderer
            ->render('pages.home', ['title' => 'Validation'], 422)
            ->withHeader('X-Trace', 'abc');

        self::assertSame(422, $response->status());
        self::assertSame('abc', $response->header('X-Trace'));
        self::assertSame('text/html; charset=utf-8', $response->header('Content-Type'));
    }

    public function test_unserializable_data_fails_clearly(): void
    {
        $this->expectException(SvelteRenderException::class);
        $this->expectExceptionMessage('could not be encoded as JSON');

        $this->renderer->render('pages.home', ['title' => 'Bad', 'value' => NAN]);
    }

    public function test_payload_is_always_a_json_object_even_when_empty(): void
    {
        $document = new SvelteDocument(
            TempProject::configuration(),
            $this->renderer->page('pages.home'),
            [],
            $this->renderer->assets(),
        );

        self::assertSame('{}', $document->payloadJson());
    }

    public function test_missing_component_fails_with_a_useful_exception(): void
    {
        $this->expectException(\App\Frontend\SveltePageNotFoundException::class);
        $this->expectExceptionMessage('src/pages/Missing.svelte');

        $this->renderer->render('pages.missing');
    }
}
