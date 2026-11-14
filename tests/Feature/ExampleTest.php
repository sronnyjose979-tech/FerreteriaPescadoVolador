<?php

test('returns a successful response', function () {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertOk();
});
test('demuestra que una prueba fallida bloquea el despliegue', function () {
    expect(false)->toBeTrue();
});
