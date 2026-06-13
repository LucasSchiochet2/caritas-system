<?php

use App\Enums\ParishRole;
use App\Models\AssistedFamilyMember;
use App\Models\BazaarCustomer;
use App\Models\BazaarItem;
use App\Models\Family;
use App\Models\Parish;
use App\Models\ParishInventory;
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

it('lets diocese admins manage bazaar customers', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/bazaar-customers', [
            'name' => 'Maria Silva',
            'birth_date' => '1988-04-20',
            'cpf' => '123.456.789-01',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Maria Silva')
        ->assertJsonPath('data.birth_date', '1988-04-20')
        ->assertJsonPath('data.cpf', '123.456.789-01');

    $customer = BazaarCustomer::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/bazaar-customers')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Maria Silva']);

    $this->withToken($token)
        ->patchJson('/api/bazaar-customers/'.$customer->id, [
            'name' => 'Maria Oliveira',
            'birth_date' => '1988-05-21',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Maria Oliveira')
        ->assertJsonPath('data.birth_date', '1988-05-21')
        ->assertJsonPath('data.cpf', '123.456.789-01');

    $this->assertDatabaseHas('bazaar_customers', [
        'id' => $customer->id,
        'name' => 'Maria Oliveira',
        'birth_date' => '1988-05-21',
        'cpf' => '123.456.789-01',
    ]);
});

it('lets diocese admins manage parish inventories from any parish', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/parish-inventories', [
            'parish_id' => $parish->id,
            'name' => 'Inventario Principal',
            'description' => 'Itens cadastrados pela diocese',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Inventario Principal')
        ->assertJsonPath('data.description', 'Itens cadastrados pela diocese');

    $inventory = ParishInventory::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/parish-inventories')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Inventario Principal']);

    $this->withToken($token)
        ->patchJson('/api/parish-inventories/'.$inventory->id, [
            'name' => 'Inventario Atualizado',
            'description' => 'Descricao atualizada',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Inventario Atualizado')
        ->assertJsonPath('data.description', 'Descricao atualizada');

    $this->assertDatabaseHas('parish_inventories', [
        'id' => $inventory->id,
        'parish_id' => $parish->id,
        'name' => 'Inventario Atualizado',
        'description' => 'Descricao atualizada',
    ]);

    $this->withToken($token)
        ->deleteJson('/api/parish-inventories/'.$inventory->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('parish_inventories', ['id' => $inventory->id]);
});

it('limits parish admins to parish inventories from their parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $ownInventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario da Paroquia',
        'description' => null,
    ]);
    $otherInventory = ParishInventory::query()->create([
        'parish_id' => $otherParish->id,
        'name' => 'Inventario Bloqueado',
        'description' => null,
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/parish-inventories')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Inventario da Paroquia'])
        ->assertJsonMissing(['name' => 'Inventario Bloqueado']);

    $this->withToken($token)
        ->postJson('/api/parish-inventories', [
            'name' => 'Inventario Novo',
            'description' => 'Criado pela paroquia',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Inventario Novo');

    $this->assertDatabaseHas('parish_inventories', [
        'parish_id' => $parish->id,
        'name' => 'Inventario Novo',
    ]);

    $this->withToken($token)
        ->patchJson('/api/parish-inventories/'.$ownInventory->id, [
            'name' => 'Inventario Editado',
            'description' => 'Editado pela paroquia',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Inventario Editado');

    $this->withToken($token)
        ->patchJson('/api/parish-inventories/'.$otherInventory->id, [
            'name' => 'Inventario Invasor',
            'description' => null,
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->deleteJson('/api/parish-inventories/'.$otherInventory->id)
        ->assertForbidden();
});

it('lets parish admins create cashboxes with their own parish id', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/cashboxes', [
            'parish_id' => $parish->id,
            'name' => 'Caixa principal',
            'balance' => 100,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Caixa principal');

    $this->assertDatabaseHas('cashboxes', [
        'parish_id' => $parish->id,
        'name' => 'Caixa principal',
    ]);

    $this->withToken($token)
        ->postJson('/api/cashboxes', [
            'parish_id' => $otherParish->id,
            'name' => 'Caixa bloqueado',
            'balance' => 100,
        ])
        ->assertForbidden();
});

it('lets diocese admins manage families from any parish', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create(['name' => 'Paroquia Sao Pedro']);
    $otherParish = Parish::factory()->create();
    $otherFamily = Family::factory()->for($otherParish)->create(['name' => 'Familia Oliveira']);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/families', [
            'parish_id' => $parish->id,
            'address' => 'Rua das Flores, 123',
            'observations' => 'Recebe cesta basica mensal',
            'responsible' => [
                'name' => 'Carla Silva',
                'mother_name' => 'Ana Silva',
                'relationship' => 'mae',
                'age' => 34,
                'registration_status' => 'ativo',
                'registration_date' => '2026-05-22',
                'personal_income' => 750.50,
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Carla Silva')
        ->assertJsonPath('data.parish_id', $parish->id)
        ->assertJsonPath('data.parish.name', 'Paroquia Sao Pedro')
        ->assertJsonPath('data.responsible.name', 'Carla Silva')
        ->assertJsonPath('data.responsible.mother_name', 'Ana Silva')
        ->assertJsonPath('data.responsible.relationship', 'mae')
        ->assertJsonPath('data.responsible.age', 34)
        ->assertJsonPath('data.responsible.is_responsible', true)
        ->assertJsonPath('data.assisted_family_members.0.mother_name', 'Ana Silva');

    $family = Family::query()->where('name', 'Carla Silva')->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/families')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->withToken($token)
        ->getJson('/api/families?all=true')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Carla Silva'])
        ->assertJsonFragment(['name' => 'Familia Oliveira']);

    $this->withToken($token)
        ->getJson('/api/families?all=true&search=Carla')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Carla Silva'])
        ->assertJsonMissing(['name' => 'Familia Oliveira']);

    $this->withToken($token)
        ->patchJson('/api/families/'.$family->id, [
            'address' => 'Rua Nova, 456',
            'parish_id' => $otherParish->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Carla Silva')
        ->assertJsonPath('data.address', 'Rua Nova, 456')
        ->assertJsonPath('data.parish_id', $otherParish->id);

    $this->assertDatabaseHas('families', [
        'id' => $family->id,
        'name' => 'Carla Silva',
        'parish_id' => $otherParish->id,
    ]);

    $this->assertDatabaseHas('assisted_family_members', [
        'family_id' => $family->id,
        'parish_id' => $otherParish->id,
        'name' => 'Carla Silva',
        'mother_name' => 'Ana Silva',
        'relationship' => 'mae',
        'age' => 34,
        'is_responsible' => true,
    ]);

    $this->withToken($token)
        ->deleteJson('/api/families/'.$otherFamily->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('families', ['id' => $otherFamily->id]);
});

it('inactivates families and hides them from listings', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $family = Family::factory()->for($parish)->create(['name' => 'Familia Ativa']);
    $otherFamily = Family::factory()->for($otherParish)->create(['name' => 'Familia Fora']);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/families/'.$family->id.'/inactivate')
        ->assertNoContent();

    $this->assertDatabaseHas('families', [
        'id' => $family->id,
        'is_active' => false,
    ]);

    $this->withToken($token)
        ->getJson('/api/families')
        ->assertOk()
        ->assertJsonMissing(['name' => 'Familia Ativa']);

    $this->withToken($token)
        ->patchJson('/api/families/'.$otherFamily->id.'/inactivate')
        ->assertForbidden();
});

it('limits parish admins to families from their parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $ownFamily = Family::factory()->for($parish)->create(['name' => 'Familia Souza']);
    $otherFamily = Family::factory()->for($otherParish)->create(['name' => 'Familia Costa']);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/families')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Familia Souza'])
        ->assertJsonMissing(['name' => 'Familia Costa']);

    $this->withToken($token)
        ->getJson('/api/families?all=true')
        ->assertForbidden();

    $this->withToken($token)
        ->getJson('/api/families?search=Souza')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Familia Souza'])
        ->assertJsonMissing(['name' => 'Familia Costa']);

    $this->withToken($token)
        ->postJson('/api/families', [
            'address' => 'Rua Central, 10',
            'responsible' => [
                'name' => 'Joana Almeida',
                'mother_name' => 'Joana Almeida',
                'relationship' => 'mae',
                'age' => 42,
                'registration_status' => 'ativo',
                'registration_date' => '2026-05-22',
                'personal_income' => 450,
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Joana Almeida')
        ->assertJsonPath('data.parish_id', $parish->id)
        ->assertJsonPath('data.responsible.name', 'Joana Almeida')
        ->assertJsonPath('data.responsible.mother_name', 'Joana Almeida');

    $this->assertDatabaseHas('families', [
        'name' => 'Joana Almeida',
        'parish_id' => $parish->id,
    ]);

    $this->withToken($token)
        ->patchJson('/api/families/'.$ownFamily->id, ['observations' => 'Atualizada'])
        ->assertOk()
        ->assertJsonPath('data.observations', 'Atualizada');

    $this->withToken($token)
        ->patchJson('/api/families/'.$otherFamily->id, ['name' => 'Bloqueada'])
        ->assertForbidden();

    $this->withToken($token)
        ->postJson('/api/families', [
            'parish_id' => $otherParish->id,
            'name' => 'Familia Fora do Escopo',
            'responsible' => [
                'name' => 'Responsavel Bloqueado',
                'mother_name' => 'Responsavel Bloqueado',
                'relationship' => 'pai',
                'age' => 40,
                'registration_status' => 'ativo',
                'registration_date' => '2026-05-22',
                'personal_income' => 0,
            ],
        ])
        ->assertForbidden();
});

it('requires a responsible assisted member when creating a family', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/families', [
            'parish_id' => $parish->id,
            'name' => 'Familia Sem Responsavel',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['responsible']);
});

it('requires the responsible person name and mother name when creating a family', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/families', [
            'parish_id' => $parish->id,
            'name' => 'Familia Incompleta',
            'responsible' => [
                'relationship' => 'mae',
                'age' => 34,
                'registration_status' => 'ativo',
                'registration_date' => '2026-05-22',
                'personal_income' => 750.50,
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['responsible.name', 'responsible.mother_name']);
});

it('lets admins manage assisted family members inside families', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $family = Family::factory()->create(['name' => 'Familia Ferreira']);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/families/'.$family->id.'/assisted-family-members', [
            'name' => 'Julia Ferreira',
            'mother_name' => 'Ana Ferreira',
            'relationship' => 'filha',
            'age' => 12,
            'registration_status' => 'ativo',
            'registration_date' => '2026-05-22',
            'personal_income' => 750.50,
        ])
        ->assertCreated()
        ->assertJsonPath('data.family_id', $family->id)
        ->assertJsonPath('data.parish_id', $family->parish_id)
        ->assertJsonPath('data.name', 'Julia Ferreira')
        ->assertJsonPath('data.mother_name', 'Ana Ferreira')
        ->assertJsonPath('data.relationship', 'filha')
        ->assertJsonPath('data.age', 12)
        ->assertJsonPath('data.registration_status', 'ativo')
        ->assertJsonPath('data.registration_date', '2026-05-22');

    $member = AssistedFamilyMember::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/families/'.$family->id.'/assisted-family-members')
        ->assertOk()
        ->assertJsonFragment(['mother_name' => 'Ana Ferreira']);

    $this->withToken($token)
        ->getJson('/api/families?all=true')
        ->assertOk()
        ->assertJsonPath('data.0.assisted_family_members.0.mother_name', 'Ana Ferreira');

    $this->withToken($token)
        ->patchJson('/api/assisted-family-members/'.$member->id, [
            'name' => 'Julio Ferreira',
            'relationship' => 'filho',
            'age' => 13,
            'registration_status' => 'inativo',
            'personal_income' => 900,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Julio Ferreira')
        ->assertJsonPath('data.relationship', 'filho')
        ->assertJsonPath('data.age', 13)
        ->assertJsonPath('data.registration_status', 'inativo')
        ->assertJsonPath('data.personal_income', '900.00');

    $this->assertDatabaseHas('assisted_family_members', [
        'id' => $member->id,
        'family_id' => $family->id,
        'parish_id' => $family->parish_id,
        'name' => 'Julio Ferreira',
        'relationship' => 'filho',
        'age' => 13,
        'registration_status' => 'inativo',
    ]);

    $this->withToken($token)
        ->deleteJson('/api/assisted-family-members/'.$member->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('assisted_family_members', ['id' => $member->id]);
});

it('prevents deleting the responsible assisted family member directly', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $family = Family::factory()->create();
    $responsible = AssistedFamilyMember::factory()->responsible()->for($family, 'family')->create([
        'parish_id' => $family->parish_id,
    ]);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/assisted-family-members/'.$responsible->id, [
            'name' => 'Responsavel Atualizado',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Responsavel Atualizado');

    $this->assertDatabaseHas('families', [
        'id' => $family->id,
        'name' => 'Responsavel Atualizado',
    ]);

    $this->withToken($token)
        ->deleteJson('/api/assisted-family-members/'.$responsible->id)
        ->assertUnprocessable();

    $this->assertDatabaseHas('assisted_family_members', [
        'id' => $responsible->id,
        'is_responsible' => true,
    ]);
});

it('limits parish admins to assisted members from their parish families', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $family = Family::factory()->for($parish)->create();
    $otherFamily = Family::factory()->for($otherParish)->create();
    $otherMember = AssistedFamilyMember::factory()->for($otherFamily, 'family')->create([
        'parish_id' => $otherParish->id,
        'mother_name' => 'Maria Bloqueada',
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/families/'.$family->id.'/assisted-family-members', [
            'name' => 'Joana Souza',
            'mother_name' => 'Joana Souza',
            'relationship' => 'mae',
            'age' => 38,
            'registration_status' => 'ativo',
            'registration_date' => '2026-05-22',
            'personal_income' => 450,
        ])
        ->assertCreated()
        ->assertJsonPath('data.family_id', $family->id)
        ->assertJsonPath('data.parish_id', $parish->id);

    $this->withToken($token)
        ->getJson('/api/families/'.$otherFamily->id.'/assisted-family-members')
        ->assertForbidden();

    $this->withToken($token)
        ->patchJson('/api/assisted-family-members/'.$otherMember->id, [
            'registration_status' => 'ativo',
        ])
        ->assertForbidden();
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

it('prevents parish tokens from managing bazaar customers', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create(['password' => 'password']);
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)->postJson('/api/bazaar-customers', [
        'name' => 'Cliente bloqueado',
        'birth_date' => '1990-01-01',
        'cpf' => '987.654.321-00',
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
