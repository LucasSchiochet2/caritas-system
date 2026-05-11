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
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $actor->isDioceseAdmin() && $actor->tokenCan('diocese');

        abort_unless($isDioceseScope || $parishScopeId !== null, 403);

        if (! $isDioceseScope) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $users = User::query()
            ->with('parishes:id,name,slug,active')
            ->when(! $isDioceseScope, function ($query) use ($parishScopeId) {
                $query->whereHas('parishes', fn ($query) => $query->whereKey($parishScopeId));
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->payload($user));

        return response()->json(['data' => $users]);
    }

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
            'data' => $this->payload($user),
        ], 201);
    }

    public function updateMe(Request $request): JsonResponse
    {
        abort_if($request->hasAny(['system_role', 'parish_ids', 'parish_role']), 403);

        return $this->update($request, $request->user());
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $actor->isDioceseAdmin() && $actor->tokenCan('diocese');
        $canManageUser = $this->canManageUser($request, $user);
        $isSelf = $actor->is($user);

        abort_unless($canManageUser || $isSelf, 403);

        if ($request->hasAny(['system_role', 'parish_ids', 'parish_role'])) {
            abort_unless($canManageUser, 403);
        }

        if (! $isDioceseScope && $request->hasAny(['system_role', 'parish_ids'])) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'system_role' => ['sometimes', Rule::enum(UserRole::class)],
            'parish_ids' => ['sometimes', 'array'],
            'parish_ids.*' => ['integer', 'exists:parishes,id'],
            'parish_role' => ['sometimes', Rule::enum(ParishRole::class)],
        ]);

        $user->fill(collect($data)->only(['name', 'email', 'password'])->all());

        if ($isDioceseScope && array_key_exists('system_role', $data)) {
            $user->system_role = UserRole::tryFrom($data['system_role']) ?? UserRole::User;
        }

        $user->save();

        if ($isDioceseScope && array_key_exists('parish_ids', $data)) {
            $parishRole = ParishRole::tryFrom($data['parish_role'] ?? ParishRole::Admin->value) ?? ParishRole::Admin;

            $user->parishes()->syncWithPivotValues(array_values(array_unique($data['parish_ids'])), [
                'role' => $parishRole->value,
            ]);
        } elseif ($canManageUser && $parishScopeId !== null && array_key_exists('parish_role', $data)) {
            $parishRole = ParishRole::tryFrom($data['parish_role']) ?? ParishRole::Admin;

            $user->parishes()->updateExistingPivot($parishScopeId, [
                'role' => $parishRole->value,
            ]);
        }

        $user->load('parishes:id,name,slug,active');

        return response()->json(['data' => $this->payload($user)]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->is($user), 403);
        abort_unless($this->canManageUser($request, $user), 403);

        $user->delete();

        return response()->json(null, 204);
    }

    private function canManageUser(Request $request, User $user): bool
    {
        $actor = $request->user();

        if ($actor->isDioceseAdmin() && $actor->tokenCan('diocese')) {
            return true;
        }

        $parishScopeId = $this->parishScopeId($request);

        if ($parishScopeId === null || ! $actor->canManageParish($parishScopeId) || $user->isDioceseAdmin()) {
            return false;
        }

        return $user->parishes()->whereKey($parishScopeId)->exists();
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

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'system_role' => $user->system_role->value,
            'parishes' => $user->relationLoaded('parishes')
                ? $user->parishes->map(fn (Parish $parish) => [
                    'id' => $parish->id,
                    'name' => $parish->name,
                    'slug' => $parish->slug,
                    'active' => $parish->active,
                    'role' => $parish->pivot->role,
                ])->values()
                : [],
        ];
    }
}
