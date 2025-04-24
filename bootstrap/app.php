<?php

use App\Enums\ApiErrorCode;
use App\Http\Responses\API\ErrorResponse;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                try {
                    $errorCode = $statusCode >= 400 && $statusCode < 500
                        ? ApiErrorCode::from($statusCode)
                        : ApiErrorCode::SERVER_ERROR; // If not a client error, default to server error
                } catch (\ValueError $valueError) {
                    // Handle the case where the status code does not correspond to an ApiErrorCode
                    $errorCode = ApiErrorCode::SERVER_ERROR;
                }

                return new ErrorResponse($errorCode, $e->getMessage(), $statusCode);
            }
        });
    })->create();
