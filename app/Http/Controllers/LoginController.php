<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    // ESTA EXACTAMENTE COMO LO VIMOS EN CLASE (Login)
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $token = $this->auth->iniciarSesion(
            $request->validated('email'),
            $request->validated('password'),
            $request->ip(),
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at,
            'abilities' => $token->accessToken->abilities,
        ]);
    }

    // Metodo de logout
    public function logout(Request $request): JsonResponse
    {
        $this->auth->cerrarSesion($request->user());

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
