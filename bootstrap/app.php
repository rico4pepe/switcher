<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Throwable;
use App\Http\Middleware\ClientApiKeyMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
          $middleware->alias([
        'client.auth' => ClientApiKeyMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
         $exceptions->render(function (
        Throwable $e,
        Request $request
    ) {

        if (! $request->is('api/*')) {
            return null;
        }

        if ($e instanceof ValidationException) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => app()->isProduction()
    ? 'Internal server error'
    : $e->getMessage(),
        ], 500);
    });
    })->create();
