<?php

declare(strict_types=1);

namespace Tests\Frontend;

use App\Frontend\SvelteAssetException;
use App\Frontend\SvelteAssets;
use PHPUnit\Framework\TestCase;
use Tests\Support\TempProject;

final class SvelteAssetsTest extends TestCase
{
    private string $base;

    protected function setUp(): void
    {
        $this->base = TempProject::create();
    }

    protected function tearDown(): void
    {
        TempProject::remove($this->base);
    }

    public function test_development_uses_the_vite_client_without_a_manifest(): void
    {
        $assets = $this->assets();

        self::assertTrue($assets->development());
        self::assertSame('Vite dev server', $assets->modeLabel());
        self::assertSame([
            'http://127.0.0.1:5173/@vite/client',
            'http://127.0.0.1:5173/src/main.ts',
        ], $assets->scripts());
        self::assertSame([], $assets->styles());
    }

    public function test_public_hot_file_overrides_the_configured_dev_server(): void
    {
        file_put_contents($this->base.'/public/hot', "http://127.0.0.1:5199/\n");

        self::assertSame([
            'http://127.0.0.1:5199/@vite/client',
            'http://127.0.0.1:5199/src/main.ts',
        ], $this->assets()->scripts());
    }

    public function test_an_invalid_hot_file_is_rejected(): void
    {
        file_put_contents($this->base.'/public/hot', 'javascript:alert(1)');

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('is not allowed');

        $this->assets()->scripts();
    }

    public function test_production_reads_the_entry_and_imported_styles_from_the_manifest(): void
    {
        TempProject::writeManifest($this->base, [
            'src/main.ts' => [
                'file' => 'assets/main-abc123.js',
                'css' => ['assets/main-abc123.css'],
                'imports' => ['_shared.ts'],
            ],
            '_shared.ts' => [
                'file' => 'assets/shared-def456.js',
                'css' => ['assets/shared-def456.css'],
            ],
        ], [
            'assets/main-abc123.js',
            'assets/main-abc123.css',
            'assets/shared-def456.js',
            'assets/shared-def456.css',
        ]);

        $assets = $this->assets(['development' => false]);

        self::assertFalse($assets->development());
        self::assertSame('Production build', $assets->modeLabel());
        self::assertSame(['/build/assets/main-abc123.js'], $assets->scripts());
        self::assertSame([
            '/build/assets/main-abc123.css',
            '/build/assets/shared-def456.css',
        ], $assets->styles());
        self::assertSame($this->base.'/public/build/.vite/manifest.json', $assets->manifestPath());
    }

    public function test_production_falls_back_to_a_legacy_manifest_location(): void
    {
        $build = $this->base.'/public/build';
        TempProject::ensureDirectory($build.'/assets');
        file_put_contents(
            $build.'/manifest.json',
            json_encode([
                'src/main.ts' => ['file' => 'assets/main.js'],
            ], JSON_THROW_ON_ERROR),
        );
        file_put_contents($build.'/assets/main.js', '/* fixture */');

        self::assertSame(['/build/assets/main.js'], $this->assets(['development' => false])->scripts());
    }

    public function test_a_missing_manifest_names_the_build_command(): void
    {
        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('npm run build');
        $this->expectExceptionMessage('.vite/manifest.json');

        $this->assets(['development' => false])->scripts();
    }

    public function test_a_missing_entry_lists_the_available_entries(): void
    {
        TempProject::writeManifest($this->base, [
            'src/other.ts' => ['file' => 'assets/other.js'],
        ], ['assets/other.js']);

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('has no entry [src/main.ts]');
        $this->expectExceptionMessage('src/other.ts');

        $this->assets(['development' => false])->scripts();
    }

    public function test_an_entry_without_a_file_is_invalid(): void
    {
        TempProject::writeManifest($this->base, [
            'src/main.ts' => ['css' => ['assets/main.css']],
        ], ['assets/main.css']);

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('has no file');

        $this->assets(['development' => false])->scripts();
    }

    public function test_a_missing_referenced_file_is_reported(): void
    {
        TempProject::writeManifest($this->base, [
            'src/main.ts' => ['file' => 'assets/main.js'],
        ], []);

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('assets/main.js');
        $this->expectExceptionMessage('does not exist');

        $this->assets(['development' => false])->scripts();
    }

    public function test_invalid_json_in_the_manifest_is_reported(): void
    {
        $build = $this->base.'/public/build/.vite';
        TempProject::ensureDirectory($build);
        file_put_contents($build.'/manifest.json', '{not json');

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('is invalid');

        $this->assets(['development' => false])->scripts();
    }

    public function test_asset_verification_can_be_disabled_for_custom_build_layouts(): void
    {
        TempProject::writeManifest($this->base, [
            'src/main.ts' => [
                'file' => 'assets/main.js',
                'css' => ['assets/main.css'],
            ],
        ], []);

        $assets = $this->assets(['development' => false, 'verify_assets' => false]);

        self::assertSame(['/build/assets/main.js'], $assets->scripts());
        self::assertSame(['/build/assets/main.css'], $assets->styles());
    }

    public function test_asset_urls_with_quotes_are_rejected(): void
    {
        TempProject::writeManifest($this->base, [
            'src/main.ts' => ['file' => 'assets/main.js'],
        ], ['assets/main.js']);

        $this->expectException(SvelteAssetException::class);
        $this->expectExceptionMessage('is not allowed');

        $this->assets([
            'development' => false,
            'build_public_path' => 'build" onload="x',
        ])->scripts();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function assets(array $overrides = []): SvelteAssets
    {
        return new SvelteAssets(TempProject::configuration($overrides), $this->base);
    }
}
