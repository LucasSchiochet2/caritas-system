<?php

namespace App\Http\Controllers\Api;

use App\Enums\ParishRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Parish;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $actor->isDioceseAdmin() && $actor->tokenCan('diocese');

        abort_unless($isDioceseScope || $parishScopeId !== null, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'system_role' => ['sometimes', Rule::enum(UserRole::class)],
            'parish_ids' => ['sometimes', 'array'],
            'parish_ids.*' => ['integer', 'exists:parishes,id'],
            'parish_role' => ['sometimes', Rule::enum(ParishRole::class)],
        ]);

        $systemRole = $isDioceseScope
            ? UserRole::tryFrom($data['system_role'] ?? UserRole::User->value) ?? UserRole::User
            : UserRole::User;

        $parishIds = $isDioceseScope
            ? array_values(array_unique($data['parish_ids'] ?? []))
            : [$parishScopeId];

        if (! $isDioceseScope) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $parishRole = ParishRole::tryFrom($data['parish_role'] ?? ParishRole::Admin->value) ?? ParishRole::Admin;

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'system_role' => $systemRole,
        ]);

        if ($parishIds !== []) {
            $user->parishes()->syncWithPivotValues($parishIds, [
                'role' => $parishRole->value,
            ]);
        }

        $user->load('parishes:id,name,slug,active');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'system_role' => $user->system_role->value,
                'parishes' => $user->parishes->map(fn (Parish $parish) => [
                    'id' => $parish->id,
                    'name' => $parish->name,
                    'slug' => $parish->slug,
                    'active' => $parish->active,
                    'role' => $parish->pivot->role,
                ])->values(),
            ],
        ], 201);
    }

    private function parishScopeId(Request $request): ?int
    {
        $token = $request->user()->currentAccessToken();

        foreach ($token?->abilities ?? [] as $ability) {
            if (str_starts_with($ability, 'parish:')) {
                return (int) str($ability)->after('parish:')->toString();
            }
        }

        return null;
    }
}
