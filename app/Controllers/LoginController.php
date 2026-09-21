<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', $request->email)->first(); //bucsa el usuario por correo, si no existe devuelve null

        // Mensaje idéntico exista o no la cuenta
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) { // si el usuario no existe o la contraseña no coincide
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $usuario->createToken('api');

        return response()->json(['token' => $token->plainTextToken], 200);
    }
}
