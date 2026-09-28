<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EnsureJsonBodyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = $request->getContent();

        if ($request->isJson() && $content !== '' && ! json_validate($content)) {
            throw new BadRequestHttpException('El cuerpo de la solicitud no es un JSON válido.');
        }

        return $next($request);
    }
}
