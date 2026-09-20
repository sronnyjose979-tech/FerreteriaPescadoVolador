<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
     public function __invoke(Request $request): JsonResponse
    {
        $usuario = User::where('email', $request->email)->first();

        // Mensaje idéntico exista o no la cuenta
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
       // $token = $usuario->createToken($request->device_name ?? 'default'); PARA EL NOMBRE DEL DISPOSITIVO
        $token = $usuario->createToken(
            name: 'api'
            /* name: 'api',
        abilities: ['pedidos:leer', 'pedidos:escribir'],
        expiresAt: now()->addMinutes(30)*/
            //asi queda sencillo
        );

        return response()->json(['token' => $token->plainTextToken], 200);
    }
}
