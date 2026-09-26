<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    //ESTA EXACTAMENTE COMO LO VIMOS EN CLASE (Login)
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $email = $request->input('email');

        // Identifica los intentos por correo e IP
        $key = Str::transliterate(Str::lower($email) . '|' . $request->ip());

        // Permite 5 intentos por minuto
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Demasiados intentos de inicio de sesion. Intente nuevamente mas tarde.'
            ], 429);
        }

        $usuario = User::where('email', $request->email)->first(); //bucsa el usuario por correo, si no existe devuelve null

        // Mensaje identico exista o no la cuenta
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) { // si el usuario no existe o la contraseña no coincide

            // Registramos el intento fallido durante 60 segundos
            RateLimiter::hit($key, 60);

            //Mandamos el mensaje diciendo que esta mal pero no mencionamos si correo o contra
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Si el login es correcto, eliminamos los intentos fallidos anteriores
        RateLimiter::clear($key);

        //Creamos el toque y en este caso le puse que expire en dos horas
        $token = $usuario->createToken('api', ['*'], now()->addHours(2));

        return response()->json(['token' => $token->plainTextToken], 200);
    }

    //Metodo de logout
    public function logout(LoginRequest $request): JsonResponse
    {
        //Eliminamos el token actual del usuario en Sanctum(Con este solo quitamos el actual, pero quedaria el resto de tokens del usuario)
        // $request->user()->currentAccessToken()->delete();

        //Este otro nos permite borrar todos los tokens del usuario que hace logout, es mas seguro 
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
    }
}
