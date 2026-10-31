<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * Vite assets could not be resolved in the current environment.
 */
final class SvelteAssetException extends SvelteException
{
    public static function missingManifest(string $manifestPath, string $buildCommand): self
    {
        return new self(sprintf(
            'The Vite production manifest [%s] is missing. Build the Svelte application first '
            .'with `%s` (or run `npm run dev` while developing).',
            $manifestPath,
            $buildCommand,
        ));
    }

    public static function invalidManifest(string $manifestPath, string $reason): self
    {
        return new self(sprintf('The Vite manifest [%s] is invalid: %s', $manifestPath, $reason));
    }

    public static function missingEntry(string $manifestPath, string $entry, array $available): self
    {
        return new self(sprintf(
            'The Vite manifest [%s] has no entry [%s]. Available entries: %s. '
            .'Check frontend.entry in config/frontend.php and the Vite input configuration.',
            $manifestPath,
            $entry,
            $available === [] ? 'none' : implode(', ', $available),
        ));
    }

    public static function missingAsset(string $assetPath, string $entry): self
    {
        return new self(sprintf(
            'The Vite manifest references [%s] for entry [%s], but the file does not exist. '
            .'Run `npm run build` again so the manifest and build output stay in sync.',
            $assetPath,
            $entry,
        ));
    }

    public static function invalidUrl(string $url): self
    {
        return new self(sprintf(
            'Vite asset URL [%s] is not allowed. Use a root-relative path or an http(s) URL without quotes or control characters.',
            $url,
        ));
    }
}
