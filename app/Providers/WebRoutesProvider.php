<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\SecurityHeaders;
use RuntimeException;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Foundation\ServiceProvider;
use Sahil\Http\Kernel;
use Sahil\Http\Middleware\RequestIdMiddleware;
use Sahil\Http\Route;
use Sahil\Http\Router;

/**
 * Registers the HTTP surface and wires the kernel with this application's
 * debug mode and global middleware.
 */
final class WebRoutesProvider extends ServiceProvider
{
    public function register(): void
    {
        $router = $this->routeTable();

        $this->app()->container()->singleton(
            Router::class,
            static fn (): Router => $router,
        );

        $this->app()->container()->singleton(
            Kernel::class,
            static function (\Sahil\Container\Container $container): Kernel {
                $application = $container->make(ApplicationContract::class);

                return (new Kernel($container->make(Router::class), $container, $application))
                    ->useDebugMode($application->config()->get('app.debug') === true)
                    ->using(new RequestIdMiddleware())
                    ->using(new SecurityHeaders());
            },
        );
    }

    private function routeTable(): Router
    {
        $router = new Router();
        $file = dirname(__DIR__, 2).'/routes/web.php';

        if (!is_file($file)) {
            throw new RuntimeException(sprintf('routes/web.php is missing; expected it at [%s].', $file));
        }

        /** @var mixed $definitions */
        $definitions = require $file;

        if (!is_array($definitions)) {
            throw new RuntimeException('routes/web.php must return an array of Sahil\Http\Route objects.');
        }

        foreach ($definitions as $definition) {
            if (!$definition instanceof Route) {
                throw new RuntimeException(sprintf(
                    'routes/web.php must return Sahil\Http\Route entries, got %s.',
                    get_debug_type($definition),
                ));
            }

            $router->register($definition);
        }

        return $router;
    }
}
