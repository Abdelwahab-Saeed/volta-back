<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
        'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        $middleware->redirectTo(guests: '/admin/login');
        
        $middleware->append(\App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $seconds = $e->getHeaders()['Retry-After'] ?? 60;
                $minutes = ceil($seconds / 60);
                
                $message = $minutes > 1 
                    ? "لقد تجاوزت عدد المحاولات المسموح بها. يرجى المحاولة مرة أخرى بعد {$minutes} دقائق."
                    : "لقد تجاوزت عدد المحاولات المسموح بها. يرجى المحاولة مرة أخرى بعد دقيقة واحدة.";

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => null
                ], 429);
            }
        });
    })->create();
