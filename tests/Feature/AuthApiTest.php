<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

test('registra una cuenta con la contraseña derivada y el rol cajero', function () {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->postJson('/api/register', [
        'name' => 'Ana Mora',
        'email' => 'ana@example.com',
        'password' => 'Secreta#2026',
        'password_confirmation' => 'Secreta#2026',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.nombre', 'Ana Mora')
        ->assertJsonPath('data.correo', 'ana@example.com')
        ->assertJsonPath('data.roles', ['cajero'])
        ->assertJsonMissingPath('data.password');
    $usuario = User::firstWhere('email', 'ana@example.com');
    expect($usuario->password)->not->toBe('Secreta#2026');
    expect(Hash::check('Secreta#2026', $usuario->password))->toBeTrue();
});

test('rechaza con 422 una contraseña que no cumple la política', function (string $password, string $message) {
    $this->seed(RolePermissionSeeder::class);

    $response = $this->postJson('/api/register', [
        'name' => 'Ana Mora',
        'email' => 'ana@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password' => $message]);
    $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
})->with([
    'menos de 8 caracteres' => ['Ab1#', 'La contraseña debe tener al menos 8 caracteres.'],
    'sin mayúsculas' => ['secreta#2026', 'La contraseña debe contener al menos una letra mayúscula y una minúscula.'],
    'sin números' => ['Secreta#abc', 'La contraseña debe contener al menos un número.'],
    'sin símbolos' => ['Secreta2026', 'La contraseña debe contener al menos un símbolo.'],
]);

test('inicia sesión con un token que expira en dos horas y lleva las capacidades del rol', function () {
    $this->seed(RolePermissionSeeder::class);
    User::factory()->create(['email' => 'cajero@example.com'])->assignRole('cajero');
    $this->travelTo('2026-09-22 08:00:00');

    $response = $this->postJson('/api/login', ['email' => 'cajero@example.com', 'password' => 'password']);

    $response->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('expires_at', '2026-09-22T10:00:00.000000Z');
    expect($response->json('abilities'))->toEqualCanonicalizing([
        'view products',
        'view sales',
        'create sales',
        'view customers',
    ]);
    $this->getJson('/api/products', ['Authorization' => 'Bearer '.$response->json('token')])
        ->assertOk();
});

test('responde 401 con el mismo mensaje si el correo no existe o la contraseña es incorrecta', function (string $email, string $password) {
    User::factory()->create(['email' => 'cajero@example.com']);

    $response = $this->postJson('/api/login', ['email' => $email, 'password' => $password]);

    $response->assertUnauthorized()
        ->assertExactJson(['message' => 'Las credenciales proporcionadas son incorrectas.']);
    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([
    'contraseña incorrecta' => ['cajero@example.com', 'incorrecta'],
    'correo inexistente' => ['nadie@example.com', 'password'],
]);

test('bloquea el inicio de sesión con 429 después de cinco intentos fallidos', function () {
    User::factory()->create(['email' => 'cajero@example.com']);
    foreach (range(1, 5) as $intento) {
        $this->postJson('/api/login', ['email' => 'cajero@example.com', 'password' => 'incorrecta'])
            ->assertUnauthorized();
    }

    $response = $this->postJson('/api/login', ['email' => 'cajero@example.com', 'password' => 'password']);

    $response->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'Demasiados intentos. Intente nuevamente más tarde.']);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('cerrar sesión revoca el token y el siguiente uso responde 401', function () {
    $this->seed(RolePermissionSeeder::class);
    User::factory()->create(['email' => 'cajero@example.com'])->assignRole('cajero');
    $token = $this->postJson('/api/login', ['email' => 'cajero@example.com', 'password' => 'password'])->json('token');
    $headers = ['Authorization' => "Bearer {$token}"];

    $response = $this->postJson('/api/logout', [], $headers);

    $response->assertOk()
        ->assertExactJson(['message' => 'Sesión cerrada correctamente.']);
    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/products', $headers)->assertUnauthorized();
});

test('un token vencido responde 401', function () {
    $this->seed(RolePermissionSeeder::class);
    User::factory()->create(['email' => 'cajero@example.com'])->assignRole('cajero');
    $this->travelTo('2026-09-22 08:00:00');
    $token = $this->postJson('/api/login', ['email' => 'cajero@example.com', 'password' => 'password'])->json('token');
    $this->travelTo('2026-09-22 10:00:01');

    $response = $this->getJson('/api/products', ['Authorization' => "Bearer {$token}"]);

    $response->assertUnauthorized();
});

test('un token sin la capacidad requerida responde 403 aunque el rol tenga el permiso', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create()->assignRole('admin');
    $product = Product::factory()->create(['price' => 1500]);
    $headers = ['Authorization' => 'Bearer '.$admin->createToken('solo-lectura', ['view products'])->plainTextToken];
    $this->getJson("/api/products/{$product->id}", $headers)->assertOk();

    $response = $this->putJson("/api/products/{$product->id}", ['price' => 10], $headers);

    $response->assertForbidden()
        ->assertExactJson(['message' => 'No tiene permiso para realizar esta acción.']);
    expect($product->fresh()->price)->toBe('1500.00');
});

test('devuelve el usuario autenticado con sus roles y sin datos sensibles', function () {
    $bodeguero = actingAsRole('bodeguero');

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertExactJson(['data' => [
            'id' => $bodeguero->id,
            'nombre' => $bodeguero->name,
            'correo' => $bodeguero->email,
            'roles' => ['bodeguero'],
        ]]);
});
