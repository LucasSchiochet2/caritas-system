<?php

use App\Enums\ParishRole;
use App\Models\BazaarItem;
use App\Models\Parish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets diocese admins manage bazaar inventory items', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/bazaar-items', [
            'suggested_price' => 39.9,
            'name' => 'Vestido floral',
            'color' => 'Rosa',
            'size' => 'M',
            'gender' => 'feminino',
            'quantity' => 3,
            'condition' => 'seminovo',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Vestido floral')
        ->assertJsonPath('data.quantity', 3);

    $item = BazaarItem::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/bazaar-items')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Vestido floral']);

    $this->withToken($token)
        ->patchJson('/api/bazaar-items/'.$item->id, [
            'name' => 'Vestido longo floral',
            'quantity' => 1,
            'condition' => 'usado',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Vestido longo floral')
        ->assertJsonPath('data.quantity', 1)
        ->assertJsonPath('data.condition', 'usado');

    $this->assertDatabaseHas('bazaar_items', [
        'id' => $item->id,
        'name' => 'Vestido longo floral',
        'quantity' => 1,
        'condition' => 'usado',
    ]);

    $this->withToken($token)
        ->deleteJson('/api/bazaar-items/'.$item->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('bazaar_items', ['id' => $item->id]);
});

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

it('lets diocese admins list update and delete users', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $user = User::factory()->create([
        'email' => 'managed@example.com',
    ]);
    $deletedUser = User::factory()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonFragment(['email' => 'managed@example.com']);

    $this->withToken($token)
        ->patchJson('/api/users/'.$user->id, [
            'name' => 'Usuario Atualizado',
            'email' => 'atualizado@example.com',
            'system_role' => 'diocese_admin',
            'parish_ids' => [$parish->id],
            'parish_role' => ParishRole::Member->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Usuario Atualizado')
        ->assertJsonPath('data.email', 'atualizado@example.com')
        ->assertJsonPath('data.system_role', 'diocese_admin')
        ->assertJsonPath('data.parishes.0.id', $parish->id)
        ->assertJsonPath('data.parishes.0.role', ParishRole::Member->value);

    $this->assertDatabaseHas('parish_user', [
        'parish_id' => $parish->id,
        'user_id' => $user->id,
        'role' => ParishRole::Member->value,
    ]);

    $this->withToken($token)
        ->deleteJson('/api/users/'.$deletedUser->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $deletedUser->id]);
});

it('limits parish admins to users from their parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $managedUser = User::factory()->create(['email' => 'paroquia@example.com']);
    $otherUser = User::factory()->create(['email' => 'outra@example.com']);
    $dioceseAdmin = User::factory()->dioceseAdmin()->create(['email' => 'diocese@example.com']);

    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $managedUser->parishes()->attach($parish, ['role' => ParishRole::Member->value]);
    $otherUser->parishes()->attach($otherParish, ['role' => ParishRole::Member->value]);
    $dioceseAdmin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonFragment(['email' => 'paroquia@example.com'])
        ->assertJsonMissing(['email' => 'outra@example.com']);

    $this->withToken($token)
        ->patchJson('/api/users/'.$managedUser->id, [
            'name' => 'Usuario da Paroquia',
            'parish_role' => ParishRole::Admin->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Usuario da Paroquia')
        ->assertJsonPath('data.parishes.0.role', ParishRole::Admin->value);

    $this->withToken($token)
        ->patchJson('/api/users/'.$otherUser->id, ['name' => 'Bloqueado'])
        ->assertForbidden();

    $this->withToken($token)
        ->deleteJson('/api/users/'.$dioceseAdmin->id)
        ->assertForbidden();
});

it('lets authenticated users update their own basic information', function () {
    $user = User::factory()->create([
        'email' => 'eu@example.com',
        'password' => 'password',
    ]);
    $token = $user->createToken('self', ['self'])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/me', [
            'name' => 'Meu Nome',
            'email' => 'novo-eu@example.com',
            'password' => 'nova-senha',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Meu Nome')
        ->assertJsonPath('data.email', 'novo-eu@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'novo-eu@example.com',
    ]);

    $this->withToken($token)
        ->patchJson('/api/me', ['system_role' => 'diocese_admin'])
        ->assertForbidden();
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

it('prevents parish tokens from managing bazaar inventory', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create(['password' => 'password']);
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)->postJson('/api/bazaar-items', [
        'suggested_price' => 10,
        'name' => 'Item bloqueado',
        'quantity' => 1,
        'condition' => 'novo',
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
