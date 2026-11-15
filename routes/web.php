<?php

declare(strict_types=1);

use App\Http\ExampleController;
use App\Http\FormController;
use App\Http\HealthController;
use App\Http\HomeController;
use App\Http\NotFoundController;
use Sahil\Routing\Link;

/**
 * HTTP route table for the Svelte starter-kit.
 *
 * The catch-all route is deliberately last: the router matches in registration
 * order, and it renders a Svelte 404 with a real 404 status for unknown paths.
 */
return [
    Link::get('/health', [HealthController::class, 'response'])->name('health'),
    Link::get('/', [HomeController::class, 'response'])->name('home'),
    Link::get('/example', [ExampleController::class, 'response'])->name('example'),
    Link::get('/form', [FormController::class, 'show'])->name('form.show'),
    Link::post('/form', [FormController::class, 'submit'])->name('form.submit'),
    Link::get('/{path:.*}', [NotFoundController::class, 'response'])->name('not-found'),
];
