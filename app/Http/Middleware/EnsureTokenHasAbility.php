<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenHasAbility
{
    private const ACTIONS = [
        'GET' => 'view',
        'HEAD' => 'view',
        'POST' => 'create',
        'PUT' => 'update',
        'PATCH' => 'update',
        'DELETE' => 'delete',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) $request->route()?->getName();
        $action = self::ACTIONS[$request->method()] ?? null;

        if ($action === null || ! Str::contains($routeName, '.')) {
            return $next($request);
        }

        $ability = $action.' '.Str::before($routeName, '.');

        if ($request->user()?->tokenCant($ability)) {
            throw new MissingAbilityException($ability);
        }

        return $next($request);
    }
}
