<?php

use App\Enums\ParishRole;
use App\Models\Parish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in a diocese admin and creates parishes', function () {
    User::factory()->dioceseAdmin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $login = $this->postJson('/api/diocese/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $login->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('abilities.0', 'diocese');

    $token = $login->json('access_token');

    $this->withToken($token)->postJson('/api/parishes', [
        'name' => 'Paroquia Sao Jose',
        'cnpj' => null,
        'active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.slug', 'paroquia-sao-jose');

    $this->assertDatabaseHas('parishes', [
        'name' => 'Paroquia Sao Jose',
        'slug' => 'paroquia-sao-jose',
    ]);
});

it('lists only active parishes', function () {
    Parish::factory()->create(['name' => 'Ativa', 'active' => true]);
    Parish::factory()->create(['name' => 'Inativa', 'active' => false]);

    $response = $this->getJson('/api/parishes');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Ativa');
});

it('logs in a parish admin and creates users for that parish', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create([
        'email' => 'parish@example.com',
        'password' => 'password',
    ]);

    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $login = $this->postJson('/api/parish/login', [
        'email' => 'parish@example.com',
        'password' => 'password',
        'parish_id' => $parish->id,
    ]);

    $login->assertOk()
        ->assertJsonPath('abilities.0', 'parish:'.$parish->id)
        ->assertJsonPath('parish.id', $parish->id);

    $token = $login->json('access_token');

    $this->withToken($token)->postJson('/api/users', [
        'name' => 'Novo Admin',
        'email' => 'novo@example.com',
        'password' => 'password',
    ])->assertCreated()
        ->assertJsonPath('data.email', 'novo@example.com')
        ->assertJsonPath('data.parishes.0.id', $parish->id)
        ->assertJsonPath('data.parishes.0.role', ParishRole::Admin->value);
});

it('prevents parish tokens from creating parishes', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create(['password' => 'password']);
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)->postJson('/api/parishes', [
        'name' => 'Paroquia Bloqueada',
    ])->assertForbidden();
});

it('lists available roles', function () {
    $this->getJson('/api/roles')
        ->assertOk()
        ->assertJsonPath('data.system_roles.0.value', 'user')
        ->assertJsonPath('data.system_roles.1.value', 'diocese_admin')
        ->assertJsonPath('data.parish_roles.0.value', 'member')
        ->assertJsonPath('data.parish_roles.1.value', 'admin');
});
