<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BazaarItemRequest;
use App\Models\BazaarItem;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * @property-read CrudPanel $crud
 */
class BazaarItemCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()->isDioceseAdmin(), 403);

        CRUD::setModel(BazaarItem::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/bazaar-item');
        CRUD::setEntityNameStrings('item do bazar', 'itens do bazar');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('name')->label('Nome');
        CRUD::column('suggested_price')->label('Valor sugerido')->type('number')->prefix('R$ ')->decimals(2);
        CRUD::column('color')->label('Cor');
        CRUD::column('size')->label('Tamanho');
        CRUD::column('gender')->label('Sexo');
        CRUD::column('quantity')->label('Quantidade');
        CRUD::column('condition')->label('Condição');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(BazaarItemRequest::class);

        CRUD::field('suggested_price')->label('Valor sugerido')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('name')->label('Nome');
        CRUD::field('color')->label('Cor');
        CRUD::field('size')->label('Tamanho');
        CRUD::field('gender')->label('Sexo');
        CRUD::field('quantity')->label('Quantidade')->type('number')->attributes(['min' => 0, 'step' => 1]);
        CRUD::field('condition')->label('Condição');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();
    }
}