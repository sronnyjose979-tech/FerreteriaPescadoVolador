<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    private const DEFAULT_ROLE = 'cajero';

    private const MAX_ATTEMPTS = 5;

    private const TOKEN_LIFETIME_HOURS = 2;

    public function registrar(array $data): User
    {
        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $usuario->assignRole(self::DEFAULT_ROLE);
    }

    public function iniciarSesion(string $email, string $password, string $ip): NewAccessToken
    {
        // Identifica los intentos por correo e IP
        $key = Str::transliterate(Str::lower($email).'|'.$ip);

        // Permite 5 intentos por minuto
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new ThrottleRequestsException(headers: ['Retry-After' => RateLimiter::availableIn($key)]);
        }

        $usuario = User::where('email', $email)->first(); // bucsa el usuario por correo, si no existe devuelve null

        // Mensaje identico exista o no la cuenta
        if (! $usuario || ! Hash::check($password, $usuario->password)) {
            // Registramos el intento fallido durante 60 segundos
            RateLimiter::hit($key, 60);

            // Mandamos el mensaje diciendo que esta mal pero no mencionamos si correo o contra
            throw new AuthenticationException('Las credenciales proporcionadas son incorrectas.');
        }

        // Si el login es correcto, eliminamos los intentos fallidos anteriores
        RateLimiter::clear($key);

        // Creamos el toque y en este caso le puse que expire en dos horas
        return $usuario->createToken(
            'api',
            $usuario->getAllPermissions()->pluck('name')->all(),
            now()->addHours(self::TOKEN_LIFETIME_HOURS),
        );
    }

    public function cerrarSesion(User $usuario): void
    {
        // Este otro nos permite borrar todos los tokens del usuario que hace logout, es mas seguro
        $usuario->tokens()->delete();
    }
}
