<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Requests\AdminUserRequest;
use App\Models\Parish;
use App\Models\User;
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
class UserCrudController extends CrudController
{
    use CreateOperation {
        store as traitStore;
    }
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation {
        update as traitUpdate;
    }

    public function setup(): void
    {
        CRUD::setModel(User::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/user');
        CRUD::setEntityNameStrings('usuário', 'usuários');

        if (! backpack_user()->isDioceseAdmin()) {
            CRUD::addClause('whereHas', 'parishes', function ($query) {
                $query->whereIn('parishes.id', $this->allowedParishIds());
            });

            CRUD::denyAccess(['delete']);
        }
    }

    protected function setupListOperation(): void
    {
        $this->applyParishFilterFromQueryString();

        CRUD::column('name')->label('Nome');
        CRUD::column('email')->label('E-mail');
        CRUD::column('system_role')->label('Perfil')->type('enum');
        CRUD::column('parishes')->label('Paróquias')->type('relationship')->attribute('name');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(AdminUserRequest::class);

        CRUD::field('name')->label('Nome');
        CRUD::field('email')->label('E-mail')->type('email');
        CRUD::field('password')->label('Senha')->type('password');

        CRUD::field('system_role')
            ->label('Perfil do sistema')
            ->type('select_from_array')
            ->options($this->roleOptions())
            ->default(UserRole::User->value)
            ->allows_null(false);

        CRUD::field('parishes')
            ->label('Paróquias administradas')
            ->type('select_multiple')
            ->model(Parish::class)
            ->attribute('name')
            ->options(fn ($query) => $this->parishOptions($query))
            ->hint('Usuários ligados a uma paróquia entram como admin paroquial dessa paróquia.');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();

        CRUD::field('password')->hint('Deixe em branco para manter a senha atual.');
    }

    public function store()
    {
        $this->sanitizeUserRequest();

        return $this->traitStore();
    }

    public function update()
    {
        $this->sanitizeUserRequest();

        return $this->traitUpdate();
    }

    private function applyParishFilterFromQueryString(): void
    {
        $parishId = request()->integer('parish_id');

        if (! $parishId) {
            return;
        }

        abort_unless(backpack_user()->canManageParish($parishId), 403);

        CRUD::addClause('whereHas', 'parishes', function ($query) use ($parishId) {
            $query->where('parishes.id', $parishId);
        });
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        if (! backpack_user()->isDioceseAdmin()) {
            return [
                UserRole::User->value => 'Usuário',
            ];
        }

        return [
            UserRole::User->value => 'Usuário',
            UserRole::DioceseAdmin->value => 'Admin da diocese',
        ];
    }

    private function parishOptions($query)
    {
        if (backpack_user()->isDioceseAdmin()) {
            return $query->orderBy('name')->get();
        }

        return $query
            ->whereIn('id', $this->allowedParishIds())
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int>
     */
    private function allowedParishIds(): array
    {
        return backpack_user()
            ->administeredParishes()
            ->pluck('parishes.id')
            ->all();
    }

    private function sanitizeUserRequest(): void
    {
        if (! request()->filled('password')) {
            request()->request->remove('password');
        }

        if (! backpack_user()->isDioceseAdmin()) {
            $allowedParishIds = $this->allowedParishIds();
            $requestedParishIds = array_filter((array) request('parishes'));
            $parishIds = array_values(array_intersect($requestedParishIds, $allowedParishIds));

            request()->merge([
                'system_role' => UserRole::User->value,
                'parishes' => $parishIds ?: $allowedParishIds,
            ]);
        }
    }
}
