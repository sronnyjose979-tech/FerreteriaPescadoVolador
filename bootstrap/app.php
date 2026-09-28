<?php

use App\Exceptions\BusinessException;
use App\Http\Middleware\EnsureJsonBodyIsValid;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            EnsureJsonBodyIsValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontReport([BusinessException::class]);

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Los datos enviados no son válidos.',
                    'errors' => $e->errors(),
                ], $e->status);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => $e->getMessage() === 'Unauthenticated.' ? 'No autenticado.' : $e->getMessage(),
                ], 401);
            }

            $status = match (true) {
                $e instanceof HttpResponseException => null,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            if ($status === null) {
                return null;
            }

            $messages = [
                403 => 'No tiene permiso para realizar esta acción.',
                404 => 'El recurso solicitado no existe.',
                405 => 'El método HTTP no está permitido para esta ruta.',
                429 => 'Demasiados intentos. Intente nuevamente más tarde.',
                500 => 'Ocurrió un error interno. Intente de nuevo más tarde.',
            ];

            return response()->json(
                ['message' => $messages[$status] ?? ($e->getMessage() ?: 'Solicitud no válida.')],
                $status,
                $e instanceof HttpExceptionInterface ? $e->getHeaders() : [],
            );
        });
    })->create();
