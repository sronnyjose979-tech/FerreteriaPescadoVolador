<?php

use App\Models\User;
use App\Services\AuthService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

test('emite un token con las capacidades del rol que vence en dos horas', function () {
    $this->seed(RolePermissionSeeder::class);
    User::factory()->create(['email' => 'bodega@example.com'])->assignRole('bodeguero');
    $this->travelTo('2026-09-22 08:00:00');
    RateLimiter::shouldReceive('tooManyAttempts')->once()->with('bodega@example.com|127.0.0.1', 5)->andReturn(false);
    RateLimiter::shouldReceive('clear')->once()->with('bodega@example.com|127.0.0.1');

    $token = (new AuthService)->iniciarSesion('bodega@example.com', 'password', '127.0.0.1');

    expect($token->accessToken->expires_at->toDateTimeString())->toBe('2026-09-22 10:00:00');
    expect($token->accessToken->abilities)->toEqualCanonicalizing([
        'view products',
        'create products',
        'update products',
        'view purchases',
        'view suppliers',
        'create suppliers',
        'update suppliers',
    ]);
});

test('rechaza credenciales inválidas con el mismo mensaje y cuenta el intento', function (string $email, string $password, string $clave) {
    User::factory()->create(['email' => 'bodega@example.com']);
    RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
    RateLimiter::shouldReceive('hit')->once()->with($clave, 60);
    RateLimiter::shouldReceive('clear')->never();

    expect(fn () => (new AuthService)->iniciarSesion($email, $password, '127.0.0.1'))
        ->toThrow(AuthenticationException::class, 'Las credenciales proporcionadas son incorrectas.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([
    'contraseña incorrecta' => ['bodega@example.com', 'incorrecta', 'bodega@example.com|127.0.0.1'],
    'correo inexistente, con mayúsculas' => ['Nadie@Example.com', 'password', 'nadie@example.com|127.0.0.1'],
]);

test('bloquea el acceso tras demasiados intentos sin verificar la contraseña', function () {
    User::factory()->create(['email' => 'bodega@example.com']);
    RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
    RateLimiter::shouldReceive('availableIn')->once()->andReturn(42);
    Hash::shouldReceive('check')->never();

    expect(fn () => (new AuthService)->iniciarSesion('bodega@example.com', 'password', '127.0.0.1'))
        ->toThrow(function (ThrottleRequestsException $e) {
            expect($e->getStatusCode())->toBe(429);
            expect($e->getHeaders())->toBe(['Retry-After' => 42]);
        });
});

test('registra a la persona con la contraseña derivada y el rol cajero', function () {
    $this->seed(RolePermissionSeeder::class);

    $usuario = (new AuthService)->registrar([
        'name' => 'Luis Mora',
        'email' => 'luis@example.com',
        'password' => 'Secreta#2026',
    ]);

    expect($usuario->getRoleNames()->all())->toBe(['cajero']);
    expect(Hash::check('Secreta#2026', $usuario->password))->toBeTrue();
    expect($usuario->password)->not->toBe('Secreta#2026');
});

test('cerrar sesión revoca todos los tokens de la persona', function () {
    $usuario = User::factory()->create();
    $usuario->createToken('web');
    $usuario->createToken('movil');

    (new AuthService)->cerrarSesion($usuario);

    expect($usuario->tokens()->count())->toBe(0);
});
