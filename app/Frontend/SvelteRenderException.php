<?php

declare(strict_types=1);

namespace App\Frontend;

/**
 * The page data could not be serialized for the browser.
 */
final class SvelteRenderException extends SvelteException
{
    public static function unserializablePayload(string $reason): self
    {
        return new self(sprintf(
            'The Svelte page props could not be encoded as JSON: %s. '
            .'Pass only JSON-serializable values (scalars, arrays, stdClass objects) to Svelte::render().',
            $reason,
        ));
    }
}
