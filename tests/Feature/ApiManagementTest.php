<?php

use App\Enums\ParishRole;
use App\Models\AssistedFamilyMember;
use App\Models\BasketDelivery;
use App\Models\BasketTemplate;
use App\Models\BazaarCustomer;
use App\Models\BazaarItem;
use App\Models\Cashbox;
use App\Models\Family;
use App\Models\HomeVisit;
use App\Models\LogsCashbox;
use App\Models\Parish;
use App\Models\ParishInventory;
use App\Models\ParishInventoryItem;
use App\Models\ParishInventoryItemQuantity;
use App\Models\ParishInventoryRepasse;
use App\Models\ParishInventoryRepasseItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

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

it('lets diocese admins manage parish inventory items with quantities', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $inventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario da Diocese',
        'description' => null,
    ]);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/parish-inventory-items', [
            'parish_inventory_id' => $inventory->id,
            'name' => 'Arroz',
            'description' => 'Pacote 5kg',
            'quantity' => 12,
            'valid_until' => '2026-12-31',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Arroz')
        ->assertJsonPath('data.total_quantity', 12)
        ->assertJsonPath('data.quantities.0.quantity', 12)
        ->assertJsonPath('data.quantities.0.valid_until', '2026-12-31');

    $item = ParishInventoryItem::query()->firstOrFail();

    $this->assertDatabaseHas('parish_inventory_items', [
        'id' => $item->id,
        'parish_inventory_id' => $inventory->id,
        'name' => 'Arroz',
        'total_quantity' => 12,
    ]);
    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'parish_inventory_item_id' => $item->id,
        'quantity' => 12,
        'valid_until' => '2026-12-31',
    ]);

    $this->withToken($token)
        ->postJson('/api/parish-inventory-items/'.$item->id.'/quantities', [
            'quantity' => 3,
            'valid_until' => '2027-01-31',
        ])
        ->assertOk()
        ->assertJsonPath('data.total_quantity', 15)
        ->assertJsonPath('data.quantities.1.quantity', 3)
        ->assertJsonPath('data.quantities.1.valid_until', '2027-01-31');

    $this->withToken($token)
        ->getJson('/api/parish-inventory-items?parish_inventory_id='.$inventory->id)
        ->assertOk()
        ->assertJsonFragment(['name' => 'Arroz'])
        ->assertJsonFragment(['quantity' => 12])
        ->assertJsonFragment(['quantity' => 3]);

    $this->withToken($token)
        ->patchJson('/api/parish-inventory-items/'.$item->id, [
            'name' => 'Arroz branco',
            'description' => 'Pacote 5kg tipo 1',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Arroz branco')
        ->assertJsonPath('data.description', 'Pacote 5kg tipo 1')
        ->assertJsonPath('data.quantities.0.quantity', 12);

    $this->withToken($token)
        ->deleteJson('/api/parish-inventory-items/'.$item->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('parish_inventory_items', ['id' => $item->id]);
    $this->assertDatabaseMissing('parish_inventory_item_quantities', [
        'parish_inventory_item_id' => $item->id,
    ]);
});

it('limits parish admins to parish inventory items from their parish', function () {
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
    $ownItem = ParishInventoryItem::query()->create([
        'parish_inventory_id' => $ownInventory->id,
        'name' => 'Feijao',
        'description' => null,
        'total_quantity' => 4,
    ]);
    ParishInventoryItemQuantity::query()->create([
        'parish_inventory_item_id' => $ownItem->id,
        'quantity' => 4,
        'valid_until' => '2026-10-10',
    ]);
    $otherItem = ParishInventoryItem::query()->create([
        'parish_inventory_id' => $otherInventory->id,
        'name' => 'Macarrao',
        'description' => null,
        'total_quantity' => 8,
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/parish-inventory-items')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Feijao'])
        ->assertJsonMissing(['name' => 'Macarrao'])
        ->assertJsonPath('data.0.quantities.0.quantity', 4);

    $this->withToken($token)
        ->postJson('/api/parish-inventory-items', [
            'parish_inventory_id' => $ownInventory->id,
            'name' => 'Oleo',
            'description' => null,
            'quantity' => 2,
            'valid_until' => '2026-11-20',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Oleo');

    $this->withToken($token)
        ->postJson('/api/parish-inventory-items', [
            'parish_inventory_id' => $otherInventory->id,
            'name' => 'Item bloqueado',
            'description' => null,
            'quantity' => 1,
            'valid_until' => '2026-11-20',
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->postJson('/api/parish-inventory-items/'.$otherItem->id.'/quantities', [
            'quantity' => 1,
            'valid_until' => '2026-11-20',
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->patchJson('/api/parish-inventory-items/'.$otherItem->id, [
            'name' => 'Bloqueado',
            'description' => null,
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->deleteJson('/api/parish-inventory-items/'.$otherItem->id)
        ->assertForbidden();
});

it('lists inventory items by requested parish without forcing the token parish scope', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $dioceseAdmin = User::factory()->dioceseAdmin()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $ownInventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario da Paroquia',
        'description' => null,
    ]);
    $otherInventory = ParishInventory::query()->create([
        'parish_id' => $otherParish->id,
        'name' => 'Inventario de Outra Paroquia',
        'description' => null,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $ownInventory->id,
        'name' => 'Feijao',
        'description' => null,
        'total_quantity' => 4,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $otherInventory->id,
        'name' => 'Macarrao',
        'description' => null,
        'total_quantity' => 8,
    ]);

    $parishToken = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;
    $dioceseToken = $dioceseAdmin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($parishToken)
        ->getJson('/api/parish-inventory-items/'.$otherParish->id)
        ->assertOk()
        ->assertJsonFragment(['name' => 'Macarrao'])
        ->assertJsonMissing(['name' => 'Feijao']);

    $this->withToken($dioceseToken)
        ->getJson('/api/parish-inventory-items/'.$parish->id)
        ->assertOk()
        ->assertJsonFragment(['name' => 'Feijao'])
        ->assertJsonMissing(['name' => 'Macarrao']);
});

it('lets diocese admins record inventory repasses and add them to parish stock', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/parish-inventory-repasses', [
            'parish_id' => $parish->id,
            'items' => [
                [
                    'name' => 'Sem validade',
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.valid_until');

    $this->withToken($token)
        ->postJson('/api/parish-inventory-repasses', [
            'parish_id' => $parish->id,
            'delivered_at' => '2026-06-20 10:00:00',
            'notes' => 'Repasse para prestacao de contas',
            'items' => [
                [
                    'name' => 'Arroz',
                    'description' => 'Pacote 5kg',
                    'quantity' => 20,
                    'unit' => 'pacote',
                    'valid_until' => '2026-12-31',
                ],
                [
                    'name' => 'Feijao',
                    'quantity' => 10,
                    'unit' => 'kg',
                    'valid_until' => '2027-01-31',
                ],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.parish_id', $parish->id)
        ->assertJsonPath('data.created_by', $admin->id)
        ->assertJsonPath('data.movement_type', 'out')
        ->assertJsonPath('data.items.0.name', 'Arroz')
        ->assertJsonPath('data.items.0.quantity', 20)
        ->assertJsonPath('data.items.0.valid_until', '2026-12-31')
        ->assertJsonPath('data.items.1.name', 'Feijao');

    $repasse = ParishInventoryRepasse::query()->firstOrFail();

    $this->assertDatabaseHas('parish_inventory_repasses', [
        'id' => $repasse->id,
        'parish_id' => $parish->id,
        'created_by' => $admin->id,
        'movement_type' => 'out',
        'notes' => 'Repasse para prestacao de contas',
    ]);
    $this->assertDatabaseHas('parish_inventory_repasse_items', [
        'parish_inventory_repasse_id' => $repasse->id,
        'name' => 'Arroz',
        'quantity' => 20,
        'unit' => 'pacote',
    ]);
    $inventory = ParishInventory::query()
        ->where('parish_id', $parish->id)
        ->firstOrFail();
    $rice = ParishInventoryItem::query()
        ->where('parish_inventory_id', $inventory->id)
        ->where('name', 'Arroz')
        ->firstOrFail();
    $beans = ParishInventoryItem::query()
        ->where('parish_inventory_id', $inventory->id)
        ->where('name', 'Feijao')
        ->firstOrFail();

    expect($rice->total_quantity)->toBe(20)
        ->and($beans->total_quantity)->toBe(10);

    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'parish_inventory_item_id' => $rice->id,
        'quantity' => 20,
        'valid_until' => '2026-12-31',
    ]);
    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'parish_inventory_item_id' => $beans->id,
        'quantity' => 10,
        'valid_until' => '2027-01-31',
    ]);

    $this->withToken($token)
        ->getJson('/api/parish-inventory-repasses/'.$repasse->id)
        ->assertOk()
        ->assertJsonPath('data.items.0.name', 'Arroz');
});

it('limits parish inventory repasses by token scope and reserves creation to diocese', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $ownRepasse = ParishInventoryRepasse::query()->create([
        'parish_id' => $parish->id,
        'created_by' => null,
        'movement_type' => 'out',
        'delivered_at' => '2026-06-20 10:00:00',
        'notes' => 'Repasse proprio',
    ]);
    ParishInventoryRepasseItem::query()->create([
        'parish_inventory_repasse_id' => $ownRepasse->id,
        'name' => 'Arroz',
        'quantity' => 5,
        'valid_until' => '2026-12-31',
    ]);
    $otherRepasse = ParishInventoryRepasse::query()->create([
        'parish_id' => $otherParish->id,
        'created_by' => null,
        'movement_type' => 'out',
        'delivered_at' => '2026-06-20 10:00:00',
        'notes' => 'Repasse de outra paroquia',
    ]);
    ParishInventoryRepasseItem::query()->create([
        'parish_inventory_repasse_id' => $otherRepasse->id,
        'name' => 'Macarrao',
        'quantity' => 7,
        'valid_until' => '2026-12-31',
    ]);

    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/parish-inventory-repasses')
        ->assertOk()
        ->assertJsonFragment(['notes' => 'Repasse proprio'])
        ->assertJsonMissing(['notes' => 'Repasse de outra paroquia']);

    $this->withToken($token)
        ->getJson('/api/parish-inventory-repasses/'.$ownRepasse->id)
        ->assertOk()
        ->assertJsonPath('data.items.0.name', 'Arroz');

    $this->withToken($token)
        ->getJson('/api/parish-inventory-repasses/'.$otherRepasse->id)
        ->assertForbidden();

    $this->withToken($token)
        ->postJson('/api/parish-inventory-repasses', [
            'parish_id' => $parish->id,
            'items' => [
                ['name' => 'Oleo', 'quantity' => 1],
            ],
        ])
        ->assertForbidden();
});

it('summarizes expired and near-expiration parish inventory item quantities', function () {
    Carbon::setTestNow('2026-06-13 12:00:00');

    try {
        $admin = User::factory()->dioceseAdmin()->create();
        $parish = Parish::factory()->create();
        $inventory = ParishInventory::query()->create([
            'parish_id' => $parish->id,
            'name' => 'Inventario de Validades',
            'description' => null,
        ]);
        $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

        $rice = ParishInventoryItem::query()->create([
            'parish_inventory_id' => $inventory->id,
            'name' => 'Arroz',
            'description' => null,
            'total_quantity' => 18,
        ]);
        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $rice->id,
            'quantity' => 5,
            'valid_until' => '2026-06-12',
        ]);
        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $rice->id,
            'quantity' => 7,
            'valid_until' => '2026-06-15',
        ]);
        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $rice->id,
            'quantity' => 6,
            'valid_until' => '2026-07-01',
        ]);

        $beans = ParishInventoryItem::query()->create([
            'parish_inventory_id' => $inventory->id,
            'name' => 'Feijao',
            'description' => null,
            'total_quantity' => 3,
        ]);
        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $beans->id,
            'quantity' => 3,
            'valid_until' => '2026-06-13',
        ]);

        $pasta = ParishInventoryItem::query()->create([
            'parish_inventory_id' => $inventory->id,
            'name' => 'Macarrao',
            'description' => null,
            'total_quantity' => 4,
        ]);
        ParishInventoryItemQuantity::query()->create([
            'parish_inventory_item_id' => $pasta->id,
            'quantity' => 4,
            'valid_until' => '2026-06-10',
        ]);

        $validUntilResponse = $this->withToken($token)
            ->getJson('/api/valid-until-this-week')
            ->assertOk()
            ->assertJsonPath('valid_until_items_count', 2)
            ->assertJsonPath('valid_until_total_quantity', 10);

        $validUntilItems = collect($validUntilResponse->json('data'))->keyBy('name');

        expect($validUntilItems->get('Arroz')['valid_until_quantity'])->toBe(7)
            ->and($validUntilItems->get('Arroz')['quantities'])->toHaveCount(1)
            ->and($validUntilItems->get('Arroz')['quantities'][0]['valid_until'])->toBe('2026-06-15')
            ->and($validUntilItems->get('Feijao')['valid_until_quantity'])->toBe(3)
            ->and($validUntilItems->has('Macarrao'))->toBeFalse();

        $expiredResponse = $this->withToken($token)
            ->getJson('/api/expired-items')
            ->assertOk()
            ->assertJsonPath('expired_items_count', 2)
            ->assertJsonPath('expired_total_quantity', 9);

        $expiredItems = collect($expiredResponse->json('data'))->keyBy('name');

        expect($expiredItems->get('Arroz')['expired_quantity'])->toBe(5)
            ->and($expiredItems->get('Arroz')['quantities'])->toHaveCount(1)
            ->and($expiredItems->get('Arroz')['quantities'][0]['valid_until'])->toBe('2026-06-12')
            ->and($expiredItems->get('Macarrao')['expired_quantity'])->toBe(4)
            ->and($expiredItems->has('Feijao'))->toBeFalse();
    } finally {
        Carbon::setTestNow();
    }
});

it('lists missing and low stock parish inventory items', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);

    $inventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario da Paroquia',
        'description' => null,
    ]);
    $otherInventory = ParishInventory::query()->create([
        'parish_id' => $otherParish->id,
        'name' => 'Inventario de Outra Paroquia',
        'description' => null,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Arroz',
        'description' => null,
        'total_quantity' => 0,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Feijao',
        'description' => null,
        'total_quantity' => 3,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Macarrao',
        'description' => null,
        'total_quantity' => 8,
    ]);
    ParishInventoryItem::query()->create([
        'parish_inventory_id' => $otherInventory->id,
        'name' => 'Oleo',
        'description' => null,
        'total_quantity' => 1,
    ]);

    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/low-stock-items?threshold=3')
        ->assertOk()
        ->assertJsonPath('threshold', 3)
        ->assertJsonPath('missing_items_count', 1)
        ->assertJsonPath('low_stock_items_count', 2)
        ->assertJsonPath('low_stock_total_quantity', 3)
        ->assertJsonPath('data.0.name', 'Arroz')
        ->assertJsonPath('data.0.stock_status', 'missing')
        ->assertJsonPath('data.1.name', 'Feijao')
        ->assertJsonPath('data.1.stock_status', 'low')
        ->assertJsonMissing(['name' => 'Macarrao'])
        ->assertJsonMissing(['name' => 'Oleo']);
});

it('lets admins create basket templates and deliver baskets by selected validity lots', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $family = Family::factory()->for($parish)->create(['name' => 'Familia Recebedora']);
    $inventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario de Cestas',
        'description' => null,
    ]);
    $rice = ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Arroz',
        'description' => null,
        'total_quantity' => 10,
    ]);
    $riceFirstLot = ParishInventoryItemQuantity::query()->create([
        'parish_inventory_item_id' => $rice->id,
        'quantity' => 4,
        'valid_until' => '2026-07-01',
    ]);
    $riceSecondLot = ParishInventoryItemQuantity::query()->create([
        'parish_inventory_item_id' => $rice->id,
        'quantity' => 6,
        'valid_until' => '2026-08-01',
    ]);
    $beans = ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Feijao',
        'description' => null,
        'total_quantity' => 5,
    ]);
    $beansLot = ParishInventoryItemQuantity::query()->create([
        'parish_inventory_item_id' => $beans->id,
        'quantity' => 5,
        'valid_until' => '2026-07-15',
    ]);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/basket-templates', [
            'parish_id' => $parish->id,
            'name' => 'Cesta Basica',
            'description' => 'Modelo mensal',
            'items' => [
                [
                    'parish_inventory_item_id' => $rice->id,
                    'quantity' => 2,
                ],
                [
                    'parish_inventory_item_id' => $beans->id,
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Cesta Basica')
        ->assertJsonPath('data.items.0.quantity', 2);

    $template = BasketTemplate::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/basket-templates/'.$template->id)
        ->assertOk()
        ->assertJsonPath('data.items.0.available_total_quantity', 10)
        ->assertJsonPath('data.items.0.quantities.0.valid_until', '2026-07-01')
        ->assertJsonPath('data.items.0.quantities.1.valid_until', '2026-08-01');

    $this->withToken($token)
        ->postJson('/api/basket-deliveries', [
            'family_id' => $family->id,
            'basket_template_id' => $template->id,
            'delivered_at' => '2026-06-13 10:00:00',
            'notes' => 'Entrega mensal',
            'items' => [
                [
                    'parish_inventory_item_quantity_id' => $riceSecondLot->id,
                    'quantity' => 2,
                ],
                [
                    'parish_inventory_item_quantity_id' => $beansLot->id,
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.family_id', $family->id)
        ->assertJsonPath('data.basket_template_id', $template->id)
        ->assertJsonPath('data.items.0.valid_until', '2026-08-01')
        ->assertJsonPath('data.items.1.valid_until', '2026-07-15');

    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'id' => $riceFirstLot->id,
        'quantity' => 4,
    ]);
    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'id' => $riceSecondLot->id,
        'quantity' => 4,
    ]);
    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'id' => $beansLot->id,
        'quantity' => 4,
    ]);
    $this->assertDatabaseHas('parish_inventory_items', [
        'id' => $rice->id,
        'total_quantity' => 8,
    ]);

    $this->withToken($token)
        ->postJson('/api/basket-deliveries', [
            'family_id' => $family->id,
            'notes' => 'Cesta montada na hora',
            'items' => [
                [
                    'parish_inventory_item_id' => $rice->id,
                    'quantity' => 1,
                ],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.basket_template_id', null)
        ->assertJsonPath('data.items.0.valid_until', '2026-07-01');

    $this->withToken($token)
        ->postJson('/api/basket-deliveries', [
            'family_id' => $family->id,
            'basket_template_id' => $template->id,
            'notes' => 'Cesta pelo template com validade automatica',
        ])
        ->assertCreated()
        ->assertJsonPath('data.basket_template_id', $template->id)
        ->assertJsonPath('data.items.0.valid_until', '2026-07-01');

    $this->withToken($token)
        ->getJson('/api/families/'.$family->id.'/basket-deliveries')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonFragment(['family_name' => 'Familia Recebedora'])
        ->assertJsonFragment(['basket_template_name' => 'Cesta Basica']);

    expect(BasketDelivery::query()->where('family_id', $family->id)->count())->toBe(3);
});

it('requires enough stock for selected lots and automatic basket allocation', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $family = Family::factory()->for($parish)->create();
    $inventory = ParishInventory::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Inventario',
        'description' => null,
    ]);
    $rice = ParishInventoryItem::query()->create([
        'parish_inventory_id' => $inventory->id,
        'name' => 'Arroz',
        'description' => null,
        'total_quantity' => 1,
    ]);
    $riceLot = ParishInventoryItemQuantity::query()->create([
        'parish_inventory_item_id' => $rice->id,
        'quantity' => 1,
        'valid_until' => '2026-07-01',
    ]);
    $template = BasketTemplate::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Cesta Teste',
        'description' => null,
        'active' => true,
    ]);
    $template->items()->create([
        'parish_inventory_item_id' => $rice->id,
        'quantity' => 2,
    ]);
    $token = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/basket-deliveries', [
            'family_id' => $family->id,
            'basket_template_id' => $template->id,
        ])
        ->assertUnprocessable();

    $this->withToken($token)
        ->postJson('/api/basket-deliveries', [
            'family_id' => $family->id,
            'items' => [
                [
                    'parish_inventory_item_id' => $rice->id,
                    'quantity' => 2,
                ],
            ],
        ])
        ->assertUnprocessable();

    $this->assertDatabaseHas('parish_inventory_item_quantities', [
        'id' => $riceLot->id,
        'quantity' => 1,
    ]);
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
                'cpf' => '222.333.444-55',
                'birth_date' => '1992-03-14',
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
        ->assertJsonPath('data.responsible.cpf', '222.333.444-55')
        ->assertJsonPath('data.responsible.birth_date', '1992-03-14')
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
        'cpf' => '222.333.444-55',
        'birth_date' => '1992-03-14',
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

it('records cashbox movements with a family from the same parish', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $family = Family::factory()->for($parish)->create();
    $cashbox = Cashbox::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Caixa Principal',
        'balance' => 100,
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/cashboxes/'.$cashbox->id, [
            'name' => 'Caixa Principal',
            'family_id' => $family->id,
            'amount' => 20,
            'movement_type' => 'out',
            'reason' => 'Cesta basica',
        ])
        ->assertOk();

    $this->assertDatabaseHas('logs_cashboxes', [
        'cashbox_id' => $cashbox->id,
        'user_id' => $admin->id,
        'family_id' => $family->id,
        'movement_type' => 'out',
        'reason' => 'Cesta basica',
        'amount' => 20,
    ]);

    $log = LogsCashbox::query()->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/logs-cashboxes')
        ->assertOk()
        ->assertJsonPath('data.0.id', $log->id)
        ->assertJsonPath('data.0.family_id', $family->id);
});

it('lists financial records for a specific family', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $family = Family::factory()->for($parish)->create();
    $otherFamily = Family::factory()->for($otherParish)->create();
    $cashbox = Cashbox::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Caixa Principal',
        'balance' => 100,
    ]);
    $otherCashbox = Cashbox::query()->create([
        'parish_id' => $otherParish->id,
        'name' => 'Caixa Outra Paroquia',
        'balance' => 100,
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $ownLog = LogsCashbox::query()->create([
        'cashbox_id' => $cashbox->id,
        'user_id' => $admin->id,
        'family_id' => $family->id,
        'movement_type' => 'out',
        'reason' => 'Cesta basica',
        'amount' => 20,
    ]);
    LogsCashbox::query()->create([
        'cashbox_id' => $otherCashbox->id,
        'user_id' => $admin->id,
        'family_id' => $otherFamily->id,
        'movement_type' => 'out',
        'reason' => 'Registro bloqueado',
        'amount' => 30,
    ]);

    $this->withToken($token)
        ->getJson('/api/families/'.$family->id.'/financial-records')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownLog->id)
        ->assertJsonPath('data.0.family_id', $family->id)
        ->assertJsonPath('data.0.cashbox.name', 'Caixa Principal')
        ->assertJsonPath('data.0.user.id', $admin->id);

    $this->withToken($token)
        ->getJson('/api/families/'.$otherFamily->id.'/financial-records')
        ->assertForbidden();
});

it('validates cashbox movement family id against the cashbox parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $otherFamily = Family::factory()->for($otherParish)->create();
    $cashbox = Cashbox::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Caixa Principal',
        'balance' => 100,
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/cashboxes/'.$cashbox->id, [
            'name' => 'Caixa Principal',
            'family_id' => $otherFamily->id,
            'amount' => 20,
            'movement_type' => 'in',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['family_id']);

    $this->withToken($token)
        ->patchJson('/api/cashboxes/'.$cashbox->id, [
            'name' => 'Caixa Principal',
            'family_id' => 999999,
            'amount' => 20,
            'movement_type' => 'in',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['family_id']);

    $this->assertDatabaseCount('logs_cashboxes', 0);
});

it('lets parish admins manage home visits for families from their parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $family = Family::factory()->for($parish)->create();
    $otherFamily = Family::factory()->for($otherParish)->create();
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;
    $visitDate = now()->addDays(3)->setTime(14, 0)->toDateTimeString();
    $otherVisitDate = now()->addDays(4)->setTime(10, 0)->toDateTimeString();

    HomeVisit::query()->create([
        'family_id' => $otherFamily->id,
        'user_id' => $admin->id,
        'visit_date' => $otherVisitDate,
    ]);

    $this->withToken($token)
        ->postJson('/api/families/'.$family->id.'/home-visits', [
            'user_id' => $admin->id,
            'visit_date' => $visitDate,
        ])
        ->assertCreated()
        ->assertJsonPath('data.family_id', $family->id)
        ->assertJsonPath('data.user_id', $admin->id)
        ->assertJsonPath('data.visit_date', $visitDate)
        ->assertJsonPath('data.status', 'pending');

    $visit = HomeVisit::query()->where('family_id', $family->id)->firstOrFail();

    $this->withToken($token)
        ->getJson('/api/home-visits')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visit->id);

    $this->withToken($token)
        ->getJson('/api/families/'.$family->id.'/home-visits')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visit->id);

    $rescheduledDate = now()->addDays(7)->setTime(9, 30)->toDateTimeString();

    $this->withToken($token)
        ->patchJson('/api/home-visits/'.$visit->id.'/reschedule', [
            'visit_date' => $rescheduledDate,
        ])
        ->assertOk()
        ->assertJsonPath('data.visit_date', $rescheduledDate);

    $nextVisitDate = now()->addMonth()->setTime(9, 30)->toDateTimeString();

    $this->withToken($token)
        ->patchJson('/api/home-visits/'.$visit->id.'/visit-record', [
            'notes' => 'Familia recebeu a equipe.',
            'forwarding' => 'Encaminhar para acompanhamento social.',
            'next_visit_date' => $nextVisitDate,
            'status' => 'completed',
        ])
        ->assertOk()
        ->assertJsonPath('data.notes', 'Familia recebeu a equipe.')
        ->assertJsonPath('data.forwarding', 'Encaminhar para acompanhamento social.')
        ->assertJsonPath('data.next_visit_date', $nextVisitDate)
        ->assertJsonPath('data.status', 'completed');

    $this->assertDatabaseHas('home_visits', [
        'id' => $visit->id,
        'family_id' => $family->id,
        'status' => 'completed',
    ]);

    $this->withToken($token)
        ->deleteJson('/api/home-visits/'.$visit->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('home_visits', ['id' => $visit->id]);
});

it('prevents parish admins from managing home visits outside their parish', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $otherFamily = Family::factory()->for($otherParish)->create();
    $visit = HomeVisit::query()->create([
        'family_id' => $otherFamily->id,
        'user_id' => $admin->id,
        'visit_date' => now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/families/'.$otherFamily->id.'/home-visits')
        ->assertForbidden();

    $this->withToken($token)
        ->patchJson('/api/home-visits/'.$visit->id.'/reschedule', [
            'visit_date' => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
        ])
        ->assertForbidden();
});

it('prevents parish admins without visits from accessing home visits', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create();
    $admin->parishes()->attach($parish, ['role' => ParishRole::AdminNoVisits->value]);
    $family = Family::factory()->for($parish)->create();
    $visit = HomeVisit::query()->create([
        'family_id' => $family->id,
        'user_id' => $admin->id,
        'visit_date' => now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
    ]);
    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/home-visits')
        ->assertForbidden();

    $this->withToken($token)
        ->getJson('/api/families/'.$family->id.'/home-visits')
        ->assertForbidden();

    $this->withToken($token)
        ->postJson('/api/families/'.$family->id.'/home-visits', [
            'user_id' => $admin->id,
            'visit_date' => now()->addDays(3)->setTime(14, 0)->toDateTimeString(),
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->patchJson('/api/home-visits/'.$visit->id.'/reschedule', [
            'visit_date' => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
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
            'cpf' => '111.222.333-44',
            'birth_date' => '2014-05-20',
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
        ->assertJsonPath('data.cpf', '111.222.333-44')
        ->assertJsonPath('data.birth_date', '2014-05-20')
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
        ->getJson('/api/assisted-family-members/search-by-cpf?cpf=11122233344')
        ->assertOk()
        ->assertJsonPath('data.id', $member->id)
        ->assertJsonPath('data.cpf', '111.222.333-44')
        ->assertJsonPath('data.birth_date', '2014-05-20');

    $this->withToken($token)
        ->patchJson('/api/assisted-family-members/'.$member->id, [
            'name' => 'Julio Ferreira',
            'cpf' => '111.222.333-55',
            'birth_date' => '2013-05-20',
            'relationship' => 'filho',
            'age' => 13,
            'registration_status' => 'inativo',
            'personal_income' => 900,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Julio Ferreira')
        ->assertJsonPath('data.cpf', '111.222.333-55')
        ->assertJsonPath('data.birth_date', '2013-05-20')
        ->assertJsonPath('data.relationship', 'filho')
        ->assertJsonPath('data.age', 13)
        ->assertJsonPath('data.registration_status', 'inativo')
        ->assertJsonPath('data.personal_income', '900.00');

    $this->assertDatabaseHas('assisted_family_members', [
        'id' => $member->id,
        'family_id' => $family->id,
        'parish_id' => $family->parish_id,
        'name' => 'Julio Ferreira',
        'cpf' => '111.222.333-55',
        'birth_date' => '2013-05-20',
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
        'cpf' => '333.444.555-66',
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

    $this->withToken($token)
        ->getJson('/api/assisted-family-members/search-by-cpf?cpf=333.444.555-66')
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

it('logs in a parish admin without visits and keeps regular parish management access', function () {
    $parish = Parish::factory()->create();
    $admin = User::factory()->create([
        'email' => 'parish-no-visits@example.com',
        'password' => 'password',
    ]);

    $admin->parishes()->attach($parish, ['role' => ParishRole::AdminNoVisits->value]);

    $login = $this->postJson('/api/parish/login', [
        'email' => 'parish-no-visits@example.com',
        'password' => 'password',
        'parish_id' => $parish->id,
    ]);

    $login->assertOk()
        ->assertJsonPath('abilities.0', 'parish:'.$parish->id)
        ->assertJsonPath('parish.id', $parish->id);

    $token = $login->json('access_token');

    $this->withToken($token)
        ->getJson('/api/families')
        ->assertOk();

    $this->withToken($token)
        ->getJson('/api/home-visits')
        ->assertForbidden();
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

it('inactivates lists and activates users', function () {
    $admin = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();
    $user = User::factory()->create([
        'email' => 'managed-active@example.com',
        'password' => 'password',
    ]);
    $user->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $userToken = $user->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;
    $adminToken = $admin->createToken('diocese-login', ['diocese'])->plainTextToken;

    $this->withToken($adminToken)
        ->patchJson('/api/users/'.$user->id.'/inactivate')
        ->assertNoContent();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'active' => false,
    ]);
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ]);

    $this->withToken($adminToken)
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonMissing(['email' => 'managed-active@example.com']);

    $this->withToken($adminToken)
        ->getJson('/api/inactive-users')
        ->assertOk()
        ->assertJsonFragment(['email' => 'managed-active@example.com'])
        ->assertJsonFragment(['active' => false]);

    $this->postJson('/api/parish/login', [
        'email' => 'managed-active@example.com',
        'password' => 'password',
        'parish_id' => $parish->id,
    ])->assertUnprocessable();

    $this->withToken($adminToken)
        ->patchJson('/api/users/'.$user->id.'/activate')
        ->assertOk()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.email', 'managed-active@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'active' => true,
    ]);
});

it('lets parish admins inactivate and activate users from their parish only', function () {
    $parish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();
    $admin = User::factory()->create();
    $managedUser = User::factory()->create(['email' => 'paroquia-inativa@example.com']);
    $otherUser = User::factory()->create(['email' => 'outra-inativa@example.com']);

    $admin->parishes()->attach($parish, ['role' => ParishRole::Admin->value]);
    $managedUser->parishes()->attach($parish, ['role' => ParishRole::Member->value]);
    $otherUser->parishes()->attach($otherParish, ['role' => ParishRole::Member->value]);

    $token = $admin->createToken('parish-login', ['parish:'.$parish->id])->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/users/'.$managedUser->id.'/inactivate')
        ->assertNoContent();

    $this->withToken($token)
        ->getJson('/api/inactive-users')
        ->assertOk()
        ->assertJsonFragment(['email' => 'paroquia-inativa@example.com'])
        ->assertJsonMissing(['email' => 'outra-inativa@example.com']);

    $this->withToken($token)
        ->patchJson('/api/users/'.$managedUser->id.'/activate')
        ->assertOk()
        ->assertJsonPath('data.active', true);

    $this->withToken($token)
        ->patchJson('/api/users/'.$otherUser->id.'/inactivate')
        ->assertForbidden();
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
        ->assertJsonPath('data.parish_roles.1.value', 'admin')
        ->assertJsonPath('data.parish_roles.2.value', 'admin_no_visits');
});
