<?php

test('muestra la documentación de la API en el navegador', function () {
    $response = $this->get('/docs/api');

    $response->assertOk()
        ->assertSee('swagger-ui')
        ->assertSee('docs\/openapi.yaml', false);
});

test('publica la especificación OpenAPI del proyecto', function () {
    $response = $this->get('/docs/openapi.yaml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/yaml');
    expect($response->baseResponse->getFile()->getContent())->toStartWith('openapi: 3.0.3');
});
