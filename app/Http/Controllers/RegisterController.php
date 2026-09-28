<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $usuario = $this->auth->registrar($request->validated());

        return (new UserResource($usuario))
            ->additional(['message' => 'Usuario registrado correctamente.'])
            ->response()
            ->setStatusCode(201);
    }
}
