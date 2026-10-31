<?php

declare(strict_types=1);

/**
 * Application bootstrap.
 *
 * Roots the application at the project directory, loads `.env`, loads the
 * config namespace, registers the provider manifest and boots the
 * application. The public entry point and the test suite share this file.
 */

use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Foundation\Application;
use Sahil\Foundation\ProviderManifest;
use Sahil\Foundation\ServiceProvider;

require_once __DIR__.'/../vendor/autoload.php';

/**
 * Build (once per process) and return the booted Application.
 */
function sahil_bootstrap(): Application
{
    static $application = null;

    if ($application instanceof Application) {
        return $application;
    }

    $basePath = dirname(__DIR__);

    Application::free();
    $application = Application::configure($basePath);

    $application
        ->loadEnvironment('.env')
        ->loadConfiguration();

    foreach (sahil_application_providers($application) as $provider) {
        $application->register($provider);
    }

    $application->boot();

    return $application;
}

/**
 * Service providers for this installation, in registration order.
 *
 * @return list<ServiceProvider>
 */
function sahil_application_providers(ApplicationContract $application): array
{
    return ProviderManifest::load(__DIR__.'/providers.php', $application);
}

return sahil_bootstrap();
