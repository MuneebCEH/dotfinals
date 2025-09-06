<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(__DIR__ . '/../routes/channels.php')
    ->withMiddleware(function (Middleware $middleware) {
        // Keep your aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // ✅ Append to the existing "web" group (do NOT replace it)
        $middleware->appendToGroup('web', \App\Http\Middleware\AutoCheckoutOnSessionTimeout::class);

        // If you previously had:
        // $middleware->group('web', [\App\Http\Middleware\AutoCheckoutOnSessionTimeout::class]);
        // delete that line — it replaces the whole web stack and breaks $errors, CSRF, sessions, etc.
    })
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();
