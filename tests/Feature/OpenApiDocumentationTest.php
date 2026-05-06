<?php

it('serves the swagger ui page', function () {
    $this->get('/docs/api')
        ->assertOk()
        ->assertSee('swagger-ui', false);
});

it('serves the openapi json document', function () {
    $this->getJson('/docs/api/openapi.json')
        ->assertOk()
        ->assertJsonPath('openapi', '3.0.3')
        ->assertJsonPath('paths./diocese/login.post.summary', 'Login como admin da diocese')
        ->assertJsonPath('paths./parishes.post.summary', 'Cria uma paroquia')
        ->assertJsonPath('paths./roles.get.summary', 'Lista roles disponiveis')
        ->assertJsonPath('components.securitySchemes.bearerAuth.scheme', 'bearer');
});
