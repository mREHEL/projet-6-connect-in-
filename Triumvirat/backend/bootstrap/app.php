<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [
            \App\Http\Middleware\UpdateLastSeen::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions) {
        // Gérer les erreurs de VALIDATION (422)
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => true,
                    'message' => 'Les données fournies sont invalides.',
                    'errors' => $e->errors(),
                    'status' => 422
                ], 422);
            }
        });

        // Erreur d'Authentification (401)
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié.',
                    'type' => 'auth_error'
                ], 401);
            }
        });

        // Toutes les autres erreurs (404, 500, etc.)
        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $status = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                    ? $e->getStatusCode()
                    : 500;

                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'status' => $status
                ], $status);
            }
        });

    })->create();



