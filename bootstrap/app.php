<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CoordinatesDetector;
use App\Http\Middleware\Locale;
use App\Http\Middleware\SetAuthRole;
use App\Http\Middleware\SocketAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        apiPrefix:'',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(Locale::class);
        $middleware->append(CoordinatesDetector::class);
        $middleware->alias([
            'setAuthRole' => SetAuthRole::class,
            'checkRole' => CheckRole::class,
            'auth.socket' => SocketAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
