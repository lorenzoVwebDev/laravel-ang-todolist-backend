<?php

use Illuminate\Foundation\Application;
use  Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/*'
        ]);

        $middleware->encryptCookies(except: [
            'refreshToken',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })->create();
