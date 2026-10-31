<?php

declare(strict_types=1);

/**
 * Service providers for this installation, in registration order.
 *
 * Kept separate from bootstrap/app.php so tests can build the same provider
 * graph on an isolated application.
 *
 * @return list<class-string<\Sahil\Foundation\ServiceProvider>>
 */
return [
    \App\Providers\FrontendServiceProvider::class,
    \App\Providers\WebRoutesProvider::class,
];
