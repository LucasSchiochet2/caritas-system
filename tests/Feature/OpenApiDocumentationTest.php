<?php

use Illuminate\Support\Facades\Route;

it('serves the swagger ui page', function () {
    $this->get('/docs/api')
        ->assertOk()
        ->assertSee('swagger-ui', false);
});

it('loads the openapi json document through a relative url', function () {
    $this->get('/docs/api')
        ->assertOk()
        ->assertSee('url: "\/docs\/api\/openapi.json"', false)
        ->assertSee('Documentação da API', false)
        ->assertSee("'Authorize': 'Autorizar'", false)
        ->assertSee("'Try it out': 'Testar'", false);
});

it('honors forwarded https proxy headers when generating absolute urls', function () {
    Route::get('/_test/generated-docs-url', fn () => route('docs.openapi.json'));

    $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.1',
        'HTTP_HOST' => 'caritas-system-production.up.railway.app',
        'HTTP_X_FORWARDED_HOST' => 'caritas-system-production.up.railway.app',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_X_FORWARDED_PORT' => '443',
    ])
        ->get('/_test/generated-docs-url')
        ->assertOk()
        ->assertSee('https://caritas-system-production.up.railway.app/docs/api/openapi.json', false);
});

it('uses the forwarded https origin in the openapi server url', function () {
    $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.1',
        'HTTP_HOST' => 'caritas-system-production.up.railway.app',
        'HTTP_X_FORWARDED_HOST' => 'caritas-system-production.up.railway.app',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_X_FORWARDED_PORT' => '443',
    ])
        ->getJson('/docs/api/openapi.json')
        ->assertOk()
        ->assertJsonPath('servers.0.url', 'https://caritas-system-production.up.railway.app/api');
});

it('serves the openapi json document', function () {
    $this->getJson('/docs/api/openapi.json')
        ->assertOk()
        ->assertJsonPath('openapi', '3.0.3')
        ->assertJsonPath('paths./diocese/login.post.summary', 'Login como admin da diocese')
        ->assertJsonPath('paths./bazaar-customers.post.summary', 'Cadastra cliente do bazar')
        ->assertJsonPath('paths./parish-inventories.get.summary', 'Lista inventarios paroquiais')
        ->assertJsonPath('paths./parish-inventories.post.summary', 'Cria inventario paroquial')
        ->assertJsonPath('paths./parish-inventories/{parishInventory}.patch.summary', 'Atualiza inventario paroquial')
        ->assertJsonPath('paths./parishes.post.summary', 'Cria uma paróquia')
        ->assertJsonPath('paths./families.post.summary', 'Cadastra família')
        ->assertJsonPath('paths./families/{family}/inactivate.patch.summary', 'Inativa uma família')
        ->assertJsonPath('paths./families/{family}/assisted-family-members.post.summary', 'Cadastra familiar assistido')
        ->assertJsonPath('paths./roles.get.summary', 'Lista perfis disponíveis')
        ->assertJsonPath('tags.0.name', 'Autenticação')
        ->assertJsonPath('components.schemas.ParishInventory.properties.name.type', 'string')
        ->assertJsonPath('components.securitySchemes.bearerAuth.scheme', 'bearer');
});
