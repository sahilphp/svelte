<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * The HTML document for one Svelte page.
 *
 * The document is deliberately small and framework-neutral:
 *
 *  - a mount element carrying the resolved component name,
 *  - a JSON payload in a non-executable `<script type="application/json">`
 *    block, escaped so controller data can never close the element,
 *  - the Vite scripts and styles for the current environment.
 *
 * Controller data is handed to `mount()` as props by `src/main.ts`; no
 * server-rendered markup is expected to survive, so the mount element starts
 * empty and JavaScript owns the page.
 */
final class SvelteDocument
{
    /**
     * @param array<string, mixed> $props
     */
    public function __construct(
        private readonly SvelteConfiguration $configuration,
        private readonly SveltePage $page,
        private readonly array $props,
        private readonly SvelteAssets $assets,
    ) {
    }

    public function html(): string
    {
        $mountId = $this->configuration->mountId();
        $title = $this->title();

        $head = [
            '<meta charset="utf-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<meta name="generator" content="SahilPHP">',
        ];

        foreach ($this->assets->styles() as $href) {
            $head[] = sprintf('<link rel="stylesheet" href="%s">', self::escape($href));
        }

        $head[] = '<title>'.self::escape($title).'</title>';

        $body = [
            sprintf(
                '<div id="%s" data-component="%s" data-page="%s" data-framework="svelte"></div>',
                self::escape($mountId),
                self::escape($this->page->component()),
                self::escape($this->page->name()),
            ),
            sprintf(
                '<script type="application/json" id="%s-props">%s</script>',
                self::escape($mountId),
                $this->payloadJson(),
            ),
            '<noscript>This application requires JavaScript to run. Enable JavaScript and reload the page.</noscript>',
        ];

        foreach ($this->assets->scripts() as $src) {
            $body[] = sprintf('<script type="module" src="%s"></script>', self::escape($src));
        }

        return implode("\n", [
            '<!doctype html>',
            sprintf('<html lang="%s">', self::escape($this->configuration->lang())),
            '<head>',
            implode("\n", $head),
            '</head>',
            sprintf('<body data-mount-id="%s">', self::escape($mountId)),
            implode("\n", $body),
            '</body>',
            '</html>',
        ]);
    }

    /**
     * Escaped JSON payload. HEX flags make `<`, `>`, `&`, quotes and
     * apostrophes safe inside a `<script>` element regardless of the data.
     */
    public function payloadJson(): string
    {
        try {
            return json_encode(
                (object) $this->props,
                JSON_THROW_ON_ERROR
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE,
            );
        } catch (\JsonException $exception) {
            throw SvelteRenderException::unserializablePayload($exception->getMessage());
        }
    }

    private function title(): string
    {
        $value = $this->props;

        foreach (explode('.', $this->configuration->titleKey()) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $this->configuration->defaultTitle();
            }

            $value = $value[$segment];
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            $title = trim((string) $value);

            if ($title !== '') {
                return $title;
            }
        }

        return $this->configuration->defaultTitle();
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
