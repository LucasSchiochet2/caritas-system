<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ParishRequest;
use App\Models\Parish;
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
class ParishCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(Parish::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/parish');
        CRUD::setEntityNameStrings('paroquia', 'paroquias');

        if (! backpack_user()->isDioceseAdmin()) {
            CRUD::addClause('whereHas', 'admins', function ($query) {
                $query->whereKey(backpack_user()->getKey());
            });

            CRUD::denyAccess(['create', 'update', 'delete']);
        }
    }

    protected function setupListOperation(): void
    {
        CRUD::column('name')->label('Nome');
        CRUD::column('slug')->label('Slug');
        CRUD::column('cnpj')->label('CNPJ');
        CRUD::column('active')->label('Ativa')->type('boolean');

        CRUD::addButtonFromView('line', 'parish_users', 'parish_users', 'beginning');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ParishRequest::class);

        CRUD::field('name')->label('Nome');
        CRUD::field('cnpj')->label('CNPJ');
        CRUD::field('active')->label('Ativa')->type('checkbox')->default(true);
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
