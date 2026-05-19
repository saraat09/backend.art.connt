<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS avant tout middleware API
        $middleware->api(prepend: [
            \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Retourner les erreurs 404/422/500 en JSON pour l'API
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Ressource introuvable.'], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
        });
    })
    ->create();
