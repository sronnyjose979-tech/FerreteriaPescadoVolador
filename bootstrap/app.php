<?php

use App\Exceptions\BusinessException;
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Una regla de negocio violada es un error esperado del cliente, no un fallo del sistema.
        // BusinessException se renderiza con su propio método render() (409 por defecto).
        $exceptions->dontReport([BusinessException::class]);

        // Errores de la API en JSON, sin trazas de pila ni detalles internos.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                // 422, 401 y respuestas ya armadas conservan su salida por defecto, que no incluye traza.
                $e instanceof ValidationException,
                $e instanceof AuthenticationException,
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
                429 => 'Demasiadas solicitudes. Intente de nuevo más tarde.',
                500 => 'Ocurrió un error interno. Intente de nuevo más tarde.',
            ];

            return response()->json(
                ['message' => $messages[$status] ?? ($e->getMessage() ?: 'Solicitud no válida.')],
                $status,
                $e instanceof HttpExceptionInterface ? $e->getHeaders() : [],
            );
        });
    })->create();
