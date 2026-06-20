<?php

namespace App\Http\Controllers\Api;

use App\Enums\ParishRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'system_roles' => [
                    $this->role(UserRole::User->value, 'Usuario'),
                    $this->role(UserRole::DioceseAdmin->value, 'Admin da diocese'),
                ],
                'parish_roles' => [
                    $this->role(ParishRole::Member->value, 'Membro'),
                    $this->role(ParishRole::Admin->value, 'Admin da paroquia'),
                    $this->role(ParishRole::AdminNoVisits->value, 'Admin da paroquia sem visitas'),
                ],
            ],
        ]);
    }

    /**
     * @return array{value: string, label: string}
     */
    private function role(string $value, string $label): array
    {
        return [
            'value' => $value,
            'label' => $label,
        ];
    }
}
