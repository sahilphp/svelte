<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * A resolved Svelte page component.
 *
 * Page names use dots or slashes and map predictably to the component tree:
 *
 *   pages.home             -> Pages/Home.svelte             -> src/pages/Home.svelte
 *   pages.dashboard.index  -> Pages/Dashboard/Index.svelte  -> src/pages/Dashboard/Index.svelte
 *   auth.login             -> Pages/Auth/Login.svelte       -> src/pages/Auth/Login.svelte
 *
 * The leading `pages` segment is optional and omitted from the directory
 * below `src/pages`; every remaining segment is converted to PascalCase.
 * The component name is what the browser receives in `data-component` and
 * is also the key used by the client-side `import.meta.glob` loader.
 */
final class SveltePage
{
    private const NAME_PATTERN = '/^[A-Za-z0-9_-]+(?:[.\/][A-Za-z0-9_-]+)*$/';

    private function __construct(
        private readonly string $name,
        private readonly string $component,
        private readonly string $sourcePath,
    ) {
    }

    /**
     * @param string $pagesDirectory absolute path to the Svelte pages directory
     */
    public static function fromName(string $page, string $pagesDirectory): self
    {
        $page = trim($page);

        if ($page === '' || preg_match(self::NAME_PATTERN, $page) !== 1 || str_contains($page, '..')) {
            throw SveltePageNotFoundException::invalidName($page);
        }

        $segments = preg_split('#[./]#', $page) ?: [];
        $segments = array_values(array_filter($segments, static fn (string $segment): bool => $segment !== ''));

        if ($segments === []) {
            throw SveltePageNotFoundException::invalidName($page);
        }

        if (strtolower($segments[0]) === 'pages') {
            array_shift($segments);
        }

        if ($segments === []) {
            throw SveltePageNotFoundException::invalidName($page);
        }

        $directories = array_map(self::pascal(...), $segments);
        $file = array_pop($directories).'.svelte';
        $component = 'Pages/'.implode('/', [...$directories, $file]);
        $sourcePath = rtrim($pagesDirectory, '/').'/'.implode('/', [...$directories, $file]);

        if (!is_file($sourcePath)) {
            throw SveltePageNotFoundException::forPage($page, $sourcePath);
        }

        return new self($page, $component, $sourcePath);
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Component identifier sent to the browser, e.g. `Pages/Home.svelte`.
     */
    public function component(): string
    {
        return $this->component;
    }

    public function sourcePath(): string
    {
        return $this->sourcePath;
    }

    private static function pascal(string $segment): string
    {
        $words = str_replace(['-', '_'], ' ', $segment);

        return str_replace(' ', '', ucwords($words));
    }
}
