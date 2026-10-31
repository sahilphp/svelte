<?php

declare(strict_types=1);

namespace App\Frontend;

use RuntimeException;

/**
 * Base exception for every Svelte rendering failure.
 *
 * Rendering errors are configuration/programming errors, not silent
 * degradation: the page either renders with a valid payload and assets or
 * fails with a message naming the thing that is wrong.
 */
class SvelteException extends RuntimeException
{
}
