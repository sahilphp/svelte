<?php

declare(strict_types=1);

namespace Tests\Feature;

use Sahil\Testing\ApplicationTestCase;
use Sahil\Testing\TestResponse;

final class HttpTest extends ApplicationTestCase
{
    protected function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public function test_health_endpoint_reports_a_booted_application(): void
    {
        $response = $this->get('/health');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('framework', 'SahilPHP')
            ->assertJsonPath('environment', 'testing')
            ->assertJsonPath('booted', true)
            ->assertJsonPath('php', PHP_VERSION);

        self::assertNotNull($response->header('X-Request-Id'));
    }

    public function test_home_page_renders_the_svelte_document_with_php_data(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertSee('<!doctype html>')
            ->assertSee('<html lang="en">')
            ->assertSee('<title>SahilPHP + Svelte</title>')
            ->assertSee('data-component="Pages/Home.svelte"')
            ->assertSee('data-page="pages.home"')
            ->assertSee('http://127.0.0.1:5173/@vite/client')
            ->assertSee('<noscript>');

        $payload = self::payload($response);

        self::assertSame('SahilPHP + Svelte', $payload['title']);
        self::assertSame('testing', $payload['environment']);
        self::assertSame(PHP_VERSION, $payload['php_version']);
        self::assertSame('Svelte 5 + TypeScript + Vite', $payload['frontend']);
        self::assertSame('Vite dev server', $payload['asset_mode']);
        self::assertCount(4, $payload['features']);
        self::assertSame('Controller data as props', $payload['features'][0]['title']);
    }

    public function test_example_page_receives_nested_data_and_request_metadata(): void
    {
        $response = $this->get('/example?tab=payload');

        $response->assertOk()->assertSee('data-component="Pages/Example.svelte"');

        $payload = self::payload($response);

        self::assertSame(['tab' => 'payload'], $payload['request']['query']);
        self::assertSame('GET', $payload['request']['method']);
        self::assertSame('/example', $payload['request']['path']);
        self::assertSame('Ada Lovelace', $payload['profile']['name']);
        self::assertContains('Svelte 5', $payload['profile']['skills']);
        self::assertCount(3, $payload['stats']);
        self::assertSame(['label' => 'Projects', 'value' => '12'], $payload['stats'][0]);
    }

    public function test_form_get_renders_an_empty_form(): void
    {
        $response = $this->get('/form');

        $response->assertOk()->assertSee('data-component="Pages/Form.svelte"');

        $payload = self::payload($response);

        self::assertSame(['name' => '', 'email' => '', 'message' => ''], $payload['values']);
        self::assertSame([], $payload['errors']);
        self::assertNull($payload['success']);
        self::assertSame(200, $payload['status']);
    }

    public function test_form_post_with_invalid_values_returns_422_and_preserves_input(): void
    {
        $response = $this->post('/form', [
            'name' => ' A ',
            'email' => 'not-an-email',
            'message' => 'short',
        ]);

        $response->assertUnprocessable()->assertSee('data-component="Pages/Form.svelte"');

        $payload = self::payload($response);

        self::assertSame(['name' => 'A', 'email' => 'not-an-email', 'message' => 'short'], $payload['values']);
        self::assertArrayHasKey('name', $payload['errors']);
        self::assertArrayHasKey('email', $payload['errors']);
        self::assertArrayHasKey('message', $payload['errors']);
        self::assertSame('Enter a valid email address.', $payload['errors']['email']);
        self::assertNull($payload['success']);
        self::assertSame(422, $payload['status']);
    }

    public function test_form_post_with_valid_values_returns_200_and_a_success_state(): void
    {
        $response = $this->post('/form', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => 'I would like to talk about Svelte integration.',
        ]);

        $response->assertOk();

        $payload = self::payload($response);

        self::assertSame([], $payload['errors']);
        self::assertIsString($payload['success']);
        self::assertStringContainsString('server-side validation', $payload['success']);
        self::assertSame(200, $payload['status']);
    }

    public function test_form_values_cannot_break_out_of_the_json_payload(): void
    {
        $attack = '</script><script>alert(1)</script>';

        $response = $this->post('/form', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => $attack,
        ]);

        $response->assertOk()->assertDontSee($attack);

        self::assertSame($attack, self::payload($response)['values']['message']);
    }

    public function test_unknown_paths_render_the_svelte_not_found_page_with_a_404(): void
    {
        $response = $this->get('/missing/page');

        $response
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('data-component="Pages/NotFound.svelte"')
            ->assertSee('<title>Page not found</title>');

        $payload = self::payload($response);

        self::assertSame('/missing/page', $payload['path']);
        self::assertSame('Page not found', $payload['title']);
    }

    public function test_assets_are_served_under_the_public_directory(): void
    {
        self::assertFileExists(dirname(__DIR__, 2).'/public/index.php');
        self::assertFileExists(dirname(__DIR__, 2).'/public/.htaccess');
        self::assertFileExists(dirname(__DIR__, 2).'/public/favicon.svg');
    }

    /**
     * @return array<string, mixed>
     */
    private static function payload(TestResponse $response): array
    {
        $matched = preg_match(
            '#<script type="application/json" id="app-props">(.*?)</script>#s',
            $response->body,
            $matches,
        );

        self::assertSame(1, $matched, 'The Svelte props payload is missing from the response.');

        /** @var mixed $decoded */
        $decoded = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded, 'The Svelte props payload must decode to an object.');

        return $decoded;
    }
}
