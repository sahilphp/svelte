<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * Resolves the scripts and stylesheets for the Svelte entry point.
 *
 * Development uses the Vite dev server (including its HMR client), while
 * production reads `public/build/.vite/manifest.json`, recursively follows
 * imported chunks for CSS, and verifies that the referenced files exist.
 * No page render ever needs a running dev server in production.
 */
final class SvelteAssets
{
    /** @var array<string, mixed>|null */
    private ?array $manifest = null;

    public function __construct(
        private readonly SvelteConfiguration $configuration,
        private readonly string $basePath,
    ) {
    }

    public function development(): bool
    {
        return $this->configuration->development();
    }

    public function modeLabel(): string
    {
        return $this->development() ? 'Vite dev server' : 'Production build';
    }

    /**
     * @return list<string>
     */
    public function scripts(): array
    {
        if ($this->development()) {
            $server = $this->developmentServer();

            return [
                $server.'/@vite/client',
                $server.'/'.ltrim($this->configuration->entry(), '/'),
            ];
        }

        $entry = $this->entryAssets();

        return [$this->publicUrl((string) $entry['file'])];
    }

    /**
     * @return list<string>
     */
    public function styles(): array
    {
        if ($this->development()) {
            // Vite injects imported CSS through the module graph in development.
            return [];
        }

        $entry = $this->entryAssets();
        $seen = [];
        $styles = [];

        $this->collectStyles($this->configuration->entry(), $seen, $styles);

        return array_map($this->publicUrl(...), $styles);
    }

    public function manifestPath(): string
    {
        return $this->buildDirectory().'/'.ltrim($this->configuration->manifest(), '/');
    }

    private function developmentServer(): string
    {
        $hotFile = $this->basePath.'/public/hot';

        if (is_file($hotFile)) {
            $hot = trim((string) file_get_contents($hotFile));

            if ($hot !== '') {
                return $this->validateUrl(rtrim($hot, '/'));
            }
        }

        return $this->validateUrl(rtrim($this->configuration->devServer(), '/'));
    }

    /**
     * @return array<string, mixed>
     */
    private function entryAssets(): array
    {
        $manifest = $this->manifest();
        $entryKey = $this->configuration->entry();

        if (!isset($manifest[$entryKey]) || !is_array($manifest[$entryKey])) {
            throw SvelteAssetException::missingEntry(
                $this->manifestPath(),
                $entryKey,
                array_keys($manifest),
            );
        }

        /** @var array<string, mixed> $entry */
        $entry = $manifest[$entryKey];

        if (!isset($entry['file']) || !is_string($entry['file']) || $entry['file'] === '') {
            throw SvelteAssetException::invalidManifest(
                $this->manifestPath(),
                sprintf('entry [%s] has no file.', $entryKey),
            );
        }

        $this->assertAssetExists($entry['file'], $entryKey);

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $path = $this->manifestPath();

        if (!is_file($path)) {
            $legacy = $this->buildDirectory().'/manifest.json';

            if (is_file($legacy)) {
                $path = $legacy;
            } else {
                throw SvelteAssetException::missingManifest($path, 'npm run build');
            }
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw SvelteAssetException::invalidManifest($path, 'the file is not readable.');
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw SvelteAssetException::invalidManifest($path, $exception->getMessage());
        }

        if (!is_array($decoded)) {
            throw SvelteAssetException::invalidManifest($path, 'the manifest must decode to an object.');
        }

        /** @var array<string, mixed> $decoded */
        $this->manifest = $decoded;

        return $this->manifest;
    }

    /**
     * @param array<string, bool> $seen
     * @param list<string> $styles
     */
    private function collectStyles(string $key, array &$seen, array &$styles): void
    {
        if (isset($seen[$key])) {
            return;
        }

        $seen[$key] = true;
        $manifest = $this->manifest();

        if (!isset($manifest[$key]) || !is_array($manifest[$key])) {
            return;
        }

        /** @var array<string, mixed> $entry */
        $entry = $manifest[$key];

        if (isset($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $css) {
                if (!is_string($css) || $css === '') {
                    continue;
                }

                $this->assertAssetExists($css, $key);
                $styles[] = $css;
            }
        }

        if (isset($entry['imports']) && is_array($entry['imports'])) {
            foreach ($entry['imports'] as $import) {
                if (is_string($import) && $import !== '') {
                    $this->collectStyles($import, $seen, $styles);
                }
            }
        }
    }

    private function publicUrl(string $file): string
    {
        $base = '/'.trim($this->configuration->buildPublicPath(), '/');
        $url = $base.'/'.ltrim($file, '/');

        return $this->validateUrl($url);
    }

    private function validateUrl(string $url): string
    {
        if ($url === '' || preg_match('/[\x00-\x1F\x7F"\'<>]/', $url) === 1) {
            throw SvelteAssetException::invalidUrl($url);
        }

        if (preg_match('#^[A-Za-z][A-Za-z0-9+.-]*:#', $url) === 1
            && preg_match('#^https?://#i', $url) !== 1
        ) {
            throw SvelteAssetException::invalidUrl($url);
        }

        return $url;
    }

    private function assertAssetExists(string $file, string $entry): void
    {
        if (!$this->configuration->verifyAssets()) {
            return;
        }

        $path = $this->buildDirectory().'/'.ltrim($file, '/');

        if (!is_file($path)) {
            throw SvelteAssetException::missingAsset($path, $entry);
        }
    }

    private function buildDirectory(): string
    {
        return rtrim($this->basePath, '/').'/'.trim($this->configuration->buildPath(), '/');
    }
}
