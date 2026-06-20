<?php

namespace Database\Seeders;

use App\Enums\ParishRole;
use App\Models\AssistedFamilyMember;
use App\Models\BasketDelivery;
use App\Models\BasketDeliveryItem;
use App\Models\BasketTemplate;
use App\Models\BasketTemplateItem;
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
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dioceseAdmin = User::query()->create([
            'name' => 'Administrador Diocese',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'system_role' => 'diocese_admin',
        ]);

        $parishNames = [
            'Paróquia Cristo Operário',
            'Paróquia Cristo Rei',
            'Paróquia e Santuário Santo Antônio',
            'Paróquia Imaculada Conceição',
            'Paróquia Jesus Bom Pastor e São João Batista',
            'Paróquia Jesus Ressuscitado',
            'Paróquia Menino Deus',
            'Paróquia Nossa Senhora da Saúde',
            'Paróquia Nossa Senhora da Saúde - Fazenda Souza',
            'Paróquia Nossa Senhora das Graças',
            'Paróquia Nossa Senhora das Graças - Arcoverde',
            'Paróquia Nossa Senhora de Caravaggio - Ana Rech',
            'Paróquia Nossa Senhora de Fátima - Bairro Cidade Nova',
            'Paróquia Nossa Senhora de Fátima - Bairro Fátima',
            'Paróquia Nossa Senhora de Lourdes',
            'Paróquia Nossa Senhora de Lourdes',
            'Paróquia Nossa Senhora do Carmo',
            'Paróquia Nossa Senhora do Rosário',
            'Paróquia Nossa Senhora do Rosário de Pompéia',
            'Paróquia Nossa Senhora do Rosário de Pompeia',
            'Paróquia Nossa Senhora do Rosário - Faria Lemos',
            'Paróquia Nossa Senhora Mãe de Deus',
            'Paróquia Sagrada Família',
            'Paróquia Sagrado Coração de Jesus',
            'Paróquia Sagrado Coração de Jesus',
            'Paróquia Sagrado Coração de Jesus',
            'Paróquia Sagrado Coração de Jesus',
            'Paróquia Santa Bárbara',
            'Paróquia Santa Catarina',
            'Paróquia Santa Cruz',
            'Paróquia Santa Lúcia',
            'Paróquia Santa Maria do Belo Horizonte',
            'Paróquia Santa Rita de Cássia',
            'Paróquia Santa Teresa',
            "Paróquia Santa Teresa D'Avila - Catedral Diocesana",
            'Paróquia Santo Antônio',
            'Paróquia Santo Antônio',
            'Paróquia Santo Antônio',
            'Paróquia Santo Antônio - Cinquentenário',
            'Paróquia Santo Antônio - Forqueta',
            'Paróquia Santo Antônio - Maragata',
            'Paróquia Santo Expedito',
            'Paróquia Santos Apóstolos',
            'Paróquia São Brás',
            'Paróquia São Ciro',
            'Paróquia São Francisco de Assis',
            'Paróquia São Francisco de Paula',
            'Paróquia São João Batista',
            'Paróquia São João Batista e Nossa Senhora Aparecida - Santuário',
            'Paróquia São Jorge',
            'Paróquia São José',
            'Paróquia São José',
            'Paróquia São José',
            'Paróquia São José - Desvio Rizzo',
            'Paróquia São Leonardo Murialdo',
            'Paróquia São Lourenço Mártir',
            'Paróquia São Luís',
            'Paróquia São Luiz Gonzaga',
            'Paróquia São Marcos',
            'Paróquia São Marcos',
            'Paróquia São Marcos - Marcorama',
            'Paróquia São Marcos - Otávio Rocha',
            'Paróquia São Pedro',
            'Paróquia São Pedro',
            'Paróquia São Pedro e São Paulo',
            'Paróquia São Pedro e São Paulo',
            'Paróquia São Pelegrino e Nossa Senhora da Pietá',
            'Paróquia São Pio X',
            'Paróquia São Roque',
            'Paróquia São Sebastião',
            'Paróquia São Tiago e Nossa Senhora de Fátima',
            'Paróquia São Vicente de Paulo',
            'Santuário de Nossa Senhora de Caravaggio',
            'Seminário Diocesano N. Sra. Aparecida',
            'Seminário Maior São João Batista',
            'Seminário Maior São José',
        ];

        $slugCounts = [];

        foreach ($parishNames as $index => $parishName) {
            $baseSlug = Str::slug($parishName);
            $slugCounts[$baseSlug] = ($slugCounts[$baseSlug] ?? 0) + 1;
            $slug = $slugCounts[$baseSlug] === 1
                ? $baseSlug
                : $baseSlug.'-'.$slugCounts[$baseSlug];

            $parish = Parish::query()->create([
                'name' => $parishName,
                'slug' => $slug,
                'active' => true,
            ]);

            $parishAdmin = User::query()->create([
                'name' => 'Admin '.$parishName,
                'email' => 'admin'.($index + 1).'@example.com',
                'password' => Hash::make('password'),
                'system_role' => 'user',
            ]);

            $member = User::query()->create([
                'name' => 'Voluntario '.$parishName,
                'email' => 'voluntario'.($index + 1).'@example.com',
                'password' => Hash::make('password'),
                'system_role' => 'user',
            ]);

            $parish->users()->attach($dioceseAdmin, ['role' => ParishRole::Admin->value]);
            $parish->users()->attach($parishAdmin, ['role' => ParishRole::Admin->value]);
            $parish->users()->attach($member, ['role' => ParishRole::Member->value]);

            $cashbox = Cashbox::query()->create([
                'parish_id' => $parish->id,
                'name' => 'Caixa Principal',
                'balance' => 0,
            ]);

            $inventory = ParishInventory::query()->create([
                'parish_id' => $parish->id,
                'name' => 'Inventário Principal',
                'description' => null,
            ]);

            if ($index !== 0) {
                continue;
            }

            $inventoryItems = collect([
                'Arroz',
                'Feijao',
                'Macarrao',
                'Oleo',
                'Leite',
                'Acucar',
                'Farinha de trigo',
                'Farinha de milho',
                'Cafe',
                'Sal',
                'Molho de tomate',
                'Sardinha',
                'Biscoito',
                'Sabonete',
                'Papel higienico',
            ])->map(function (string $name) use ($inventory): ParishInventoryItem {
                $quantity = 100;

                $item = ParishInventoryItem::query()->create([
                    'parish_inventory_id' => $inventory->id,
                    'name' => $name,
                    'description' => 'Item de cesta basica',
                    'total_quantity' => $quantity,
                ]);

                ParishInventoryItemQuantity::query()->create([
                    'parish_inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'valid_until' => now()->addMonths(8)->toDateString(),
                ]);

                return $item;
            });

            $template = BasketTemplate::query()->create([
                'parish_id' => $parish->id,
                'name' => 'Cesta basica',
                'description' => 'Modelo padrao de cesta familiar.',
                'active' => true,
            ]);

            $inventoryItems->each(fn (ParishInventoryItem $item) => BasketTemplateItem::query()->create([
                'basket_template_id' => $template->id,
                'parish_inventory_item_id' => $item->id,
                'quantity' => 1,
            ]));

            foreach (range(1, 50) as $familyIndex) {
                $familyCode = str_pad((string) $familyIndex, 2, '0', STR_PAD_LEFT);
                $responsibleAge = 28 + ($familyIndex % 35);

                $family = Family::query()->create([
                    'parish_id' => $parish->id,
                    'name' => 'Familia Cristo Operario '.$familyCode,
                    'is_active' => true,
                    'address' => 'Rua da Comunidade, '.(100 + $familyIndex),
                    'observations' => 'Cadastro inicial gerado pelo seeder.',
                ]);

                AssistedFamilyMember::query()->create([
                    'parish_id' => $parish->id,
                    'family_id' => $family->id,
                    'name' => 'Responsavel '.$familyCode,
                    'cpf' => sprintf('111.%03d.%03d-%02d', 200 + $familyIndex, 300 + $familyIndex, $familyIndex),
                    'birth_date' => now()->subYears($responsibleAge)->toDateString(),
                    'mother_name' => 'Maria Responsavel '.$familyCode,
                    'relationship' => 'responsavel',
                    'age' => $responsibleAge,
                    'registration_status' => 'ativo',
                    'registration_date' => now()->subMonths(2)->toDateString(),
                    'personal_income' => 800 + (($familyIndex % 8) * 150),
                    'is_responsible' => true,
                ]);

                foreach (range(1, 3) as $dependentIndex) {
                    $dependentAge = 5 + (($familyIndex + $dependentIndex) % 13);

                    AssistedFamilyMember::query()->create([
                        'parish_id' => $parish->id,
                        'family_id' => $family->id,
                        'name' => 'Dependente '.$familyCode.'.'.$dependentIndex,
                        'cpf' => null,
                        'birth_date' => now()->subYears($dependentAge)->toDateString(),
                        'mother_name' => 'Responsavel '.$familyCode,
                        'relationship' => 'filho',
                        'age' => $dependentAge,
                        'registration_status' => 'ativo',
                        'registration_date' => now()->subMonths(2)->toDateString(),
                        'personal_income' => 0,
                        'is_responsible' => false,
                    ]);
                }

                foreach (range(1, 2) as $visitIndex) {
                    HomeVisit::query()->create([
                        'family_id' => $family->id,
                        'user_id' => $parishAdmin->id,
                        'visit_date' => now()->subDays(($familyIndex * 2) + $visitIndex),
                        'notes' => 'Visita de acompanhamento '.$visitIndex.'.',
                        'forwarding' => 'Acompanhar mensalmente.',
                        'next_visit_date' => now()->addMonth(),
                        'status' => $visitIndex === 1 ? 'completed' : 'pending',
                    ]);
                }

                $donationAmount = 250 + ($familyIndex * 5);
                $aidAmount = 120 + ($familyIndex * 3);

                LogsCashbox::query()->create([
                    'cashbox_id' => $cashbox->id,
                    'user_id' => $parishAdmin->id,
                    'family_id' => null,
                    'movement_type' => 'in',
                    'reason' => 'Doacao para acao social',
                    'amount' => $donationAmount,
                ]);

                $cashbox->increment('balance', $donationAmount);

                LogsCashbox::query()->create([
                    'cashbox_id' => $cashbox->id,
                    'user_id' => $parishAdmin->id,
                    'family_id' => $family->id,
                    'movement_type' => 'out',
                    'reason' => 'Auxilio familiar',
                    'amount' => $aidAmount,
                ]);

                $cashbox->decrement('balance', $aidAmount);

                $delivery = BasketDelivery::query()->create([
                    'parish_id' => $parish->id,
                    'family_id' => $family->id,
                    'basket_template_id' => $template->id,
                    'created_by' => $parishAdmin->id,
                    'delivered_at' => now()->subDays($familyIndex),
                    'notes' => 'Entrega gerada pelo seeder.',
                ]);

                $template->items()->get()->each(function (BasketTemplateItem $templateItem) use ($delivery): void {
                    $quantityLot = ParishInventoryItemQuantity::query()
                        ->where('parish_inventory_item_id', $templateItem->parish_inventory_item_id)
                        ->where('quantity', '>', 0)
                        ->first();

                    if (! $quantityLot || $quantityLot->quantity < $templateItem->quantity) {
                        return;
                    }

                    BasketDeliveryItem::query()->create([
                        'basket_delivery_id' => $delivery->id,
                        'parish_inventory_item_id' => $templateItem->parish_inventory_item_id,
                        'parish_inventory_item_quantity_id' => $quantityLot->id,
                        'quantity' => $templateItem->quantity,
                    ]);

                    $quantityLot->decrement('quantity', $templateItem->quantity);
                    ParishInventoryItem::query()
                        ->whereKey($templateItem->parish_inventory_item_id)
                        ->decrement('total_quantity', $templateItem->quantity);
                });
            }
        }

        BazaarItem::factory()->count(50)->create();
        BazaarCustomer::factory()->count(50)->create();
    }
}
