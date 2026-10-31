<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * Validated frontend settings read from `config/frontend.php`.
 *
 * The value object keeps the renderer free of configuration lookups and
 * makes invalid values fail at startup (or in a focused unit test) with a
 * message naming the offending key.
 */
final class SvelteConfiguration
{
    public function __construct(
        private readonly string $mountId = 'app',
        private readonly string $titleKey = 'title',
        private readonly string $lang = 'en',
        private readonly string $defaultTitle = 'SahilPHP',
        private readonly bool $development = true,
        private readonly string $devServer = 'http://127.0.0.1:5173',
        private readonly string $buildPath = 'public/build',
        private readonly string $buildPublicPath = 'build',
        private readonly string $manifest = '.vite/manifest.json',
        private readonly string $entry = 'src/main.ts',
        private readonly bool $verifyAssets = true,
        private readonly string $pagesDirectory = 'src/pages',
    ) {
        if (preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $this->mountId) !== 1) {
            throw new SvelteException(sprintf(
                'Invalid frontend.mount_id [%s]; use letters, digits, underscores and dashes, starting with a letter.',
                $this->mountId,
            ));
        }

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $this->titleKey) !== 1) {
            throw new SvelteException(sprintf(
                'Invalid frontend.title_key [%s]; use a data key such as "title".',
                $this->titleKey,
            ));
        }

        if (preg_match('/^[A-Za-z]{2,8}(?:-[A-Za-z0-9]{2,8})*$/', $this->lang) !== 1) {
            throw new SvelteException(sprintf('Invalid frontend.lang [%s].', $this->lang));
        }

        $parts = parse_url($this->devServer);

        if ($parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
        ) {
            throw new SvelteException(sprintf(
                'Invalid frontend.dev_server [%s]; use an http(s) URL such as http://127.0.0.1:5173.',
                $this->devServer,
            ));
        }

        foreach ([
            'build_path' => $this->buildPath,
            'build_public_path' => $this->buildPublicPath,
            'manifest' => $this->manifest,
            'entry' => $this->entry,
            'pages_directory' => $this->pagesDirectory,
        ] as $key => $path) {
            if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
                throw new SvelteException(sprintf('frontend.%s [%s] must be a safe relative path.', $key, $path));
            }
        }

        if (preg_match('/^[A-Za-z0-9._\/-]+$/', $this->manifest) !== 1) {
            throw new SvelteException(sprintf('Invalid frontend.manifest [%s].', $this->manifest));
        }
    }

    /**
     * @param array<string, mixed> $configuration the `frontend` namespace
     */
    public static function fromArray(array $configuration): self
    {
        $string = static function (string $key, string $default) use ($configuration): string {
            $value = $configuration[$key] ?? $default;

            if (!is_string($value)) {
                throw new SvelteException(sprintf(
                    'frontend.%s must be a string, got %s.',
                    $key,
                    get_debug_type($value),
                ));
            }

            return $value;
        };

        $boolean = static function (string $key, bool $default) use ($configuration): bool {
            $value = $configuration[$key] ?? $default;

            if (!is_bool($value)) {
                throw new SvelteException(sprintf(
                    'frontend.%s must be a boolean, got %s.',
                    $key,
                    get_debug_type($value),
                ));
            }

            return $value;
        };

        return new self(
            mountId: $string('mount_id', 'app'),
            titleKey: $string('title_key', 'title'),
            lang: $string('lang', 'en'),
            defaultTitle: $string('default_title', 'SahilPHP'),
            development: $boolean('development', true),
            devServer: $string('dev_server', 'http://127.0.0.1:5173'),
            buildPath: $string('build_path', 'public/build'),
            buildPublicPath: $string('build_public_path', 'build'),
            manifest: $string('manifest', '.vite/manifest.json'),
            entry: $string('entry', 'src/main.ts'),
            verifyAssets: $boolean('verify_assets', true),
            pagesDirectory: $string('pages_directory', 'src/pages'),
        );
    }

    public function mountId(): string
    {
        return $this->mountId;
    }

    public function titleKey(): string
    {
        return $this->titleKey;
    }

    public function lang(): string
    {
        return $this->lang;
    }

    public function defaultTitle(): string
    {
        return $this->defaultTitle;
    }

    public function development(): bool
    {
        return $this->development;
    }

    public function devServer(): string
    {
        return $this->devServer;
    }

    public function buildPath(): string
    {
        return $this->buildPath;
    }

    public function buildPublicPath(): string
    {
        return $this->buildPublicPath;
    }

    public function manifest(): string
    {
        return $this->manifest;
    }

    public function entry(): string
    {
        return $this->entry;
    }

    public function verifyAssets(): bool
    {
        return $this->verifyAssets;
    }

    public function pagesDirectory(): string
    {
        return $this->pagesDirectory;
    }
}
