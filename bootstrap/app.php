<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RequireActiveInternship;
use App\Http\Middleware\RequireReplyLetterReceipt;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            fn (Request $request): ?string =>
                $request->is('api/*') || $request->expectsJson()
                    ? null
                    : route('login')
        );

        $middleware->alias([
            'role' => CheckRole::class,
            'active.internship' => RequireActiveInternship::class,
            'reply.letter.received' => RequireReplyLetterReceipt::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API requests should receive JSON errors (such as 401 when no
        // Sanctum token is supplied), never a redirect to a web login route.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $exception): bool =>
                $request->is('api/*') || $request->expectsJson()
        );
    })->create();
