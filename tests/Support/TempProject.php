<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Frontend\SvelteAssets;
use App\Frontend\SvelteConfiguration;
use App\Frontend\SvelteRenderer;

/**
 * Isolated filesystem fixtures for renderer and asset tests.
 *
 * Tests never depend on the developer's built `public/build` directory or on
 * a running Vite server.
 */
final class TempProject
{
    public static function create(): string
    {
        $path = sys_get_temp_dir().'/sahilphp-svelte-'.bin2hex(random_bytes(8));

        if (!mkdir($path, 0o777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Unable to create fixture [%s].', $path));
        }

        self::ensureDirectory($path.'/public');
        self::writePage($path, 'Home');
        self::writePage($path, 'NotFound');
        self::writePage($path, 'Dashboard/Index');

        return $path;
    }

    public static function remove(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }

    public static function writePage(string $base, string $relative): void
    {
        $file = $base.'/src/pages/'.$relative.'.svelte';

        self::ensureDirectory(dirname($file));
        file_put_contents($file, "<main>{$relative}</main>\n");
    }

    /**
     * @param array<string, mixed> $manifest
     * @param list<string> $files
     */
    public static function writeManifest(string $base, array $manifest, array $files): void
    {
        $build = $base.'/public/build';
        self::ensureDirectory($build.'/.vite');
        file_put_contents(
            $build.'/.vite/manifest.json',
            json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );

        foreach ($files as $file) {
            $path = $build.'/'.$file;
            self::ensureDirectory(dirname($path));
            file_put_contents($path, '/* fixture */');
        }
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public static function configuration(array $overrides = []): SvelteConfiguration
    {
        return SvelteConfiguration::fromArray(array_replace([
            'mount_id' => 'app',
            'title_key' => 'title',
            'lang' => 'en',
            'default_title' => 'SahilPHP',
            'development' => true,
            'dev_server' => 'http://127.0.0.1:5173',
            'build_path' => 'public/build',
            'build_public_path' => 'build',
            'manifest' => '.vite/manifest.json',
            'entry' => 'src/main.ts',
            'verify_assets' => true,
            'pages_directory' => 'src/pages',
        ], $overrides));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public static function renderer(string $base, array $overrides = []): SvelteRenderer
    {
        $configuration = self::configuration($overrides);

        return new SvelteRenderer(
            $configuration,
            new SvelteAssets($configuration, $base),
            $base,
        );
    }

    public static function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0o777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Unable to create directory [%s].', $path));
        }
    }
}
