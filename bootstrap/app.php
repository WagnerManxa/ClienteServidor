<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Middleware\CustomAuthMiddleware;
use App\Http\Middleware\OnlyJsonAllowed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/logout',
            '/login',
            '/usuarios/candidatos',
            '/usuario',
            '/usuarios/empresa',
            '/vagas',
            '/competencias',
            '/experiencias',
            '/competencias',
            '/usuarios',
            '/vagas',
            '/vagas/*'
        ]);
        $middleware ->api(append: [
            OnlyJsonAllowed::class,
            CustomAuthMiddleware::class,
            HandleCors::class,
        ]);
        $middleware->alias([
            'custom.auth' => CustomAuthMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
