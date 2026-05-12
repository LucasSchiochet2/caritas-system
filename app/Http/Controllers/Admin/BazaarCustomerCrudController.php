<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BazaarCustomerRequest;
use App\Models\BazaarCustomer;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * @property-read CrudPanel $crud
 */
class BazaarCustomerCrudController extends CrudController
{
    use CreateOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()->isDioceseAdmin(), 403);

        CRUD::setModel(BazaarCustomer::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/bazaar-customer');
        CRUD::setEntityNameStrings('cliente do bazar', 'clientes do bazar');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('name')->label('Nome');
        CRUD::column('birth_date')->label('Data de nascimento')->type('date');
        CRUD::column('cpf')->label('CPF');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(BazaarCustomerRequest::class);

        CRUD::field('name')->label('Nome');
        CRUD::field('birth_date')->label('Data de nascimento')->type('date');
        CRUD::field('cpf')->label('CPF')->attributes(['maxlength' => 14]);
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
