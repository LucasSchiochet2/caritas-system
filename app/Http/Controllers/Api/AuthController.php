<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parish;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function dioceseLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->attempt($credentials['email'], $credentials['password']);

        if (! $user->isDioceseAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['Este usuario nao tem acesso administrativo da diocese.'],
            ]);
        }

        return $this->tokenResponse($user, 'diocese-login', ['diocese']);
    }

    public function parishLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'parish_id' => ['required_without:parish_slug', 'nullable', 'integer', 'exists:parishes,id'],
            'parish_slug' => ['required_without:parish_id', 'nullable', 'string', 'exists:parishes,slug'],
        ]);

        $user = $this->attempt($credentials['email'], $credentials['password']);
        $parish = Parish::query()
            ->where('active', true)
            ->when($request->filled('parish_id'), fn ($query) => $query->whereKey($credentials['parish_id']))
            ->when($request->filled('parish_slug'), fn ($query) => $query->where('slug', $credentials['parish_slug']))
            ->firstOrFail();

        if (! $user->canManageParish($parish)) {
            throw ValidationException::withMessages([
                'email' => ['Este usuario nao administra a paroquia informada.'],
            ]);
        }

        return $this->tokenResponse($user, 'parish-login', ['parish:'.$parish->getKey()], $parish);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('parishes:id,name,slug,active');

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    private function attempt(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        return $user;
    }

    /**
     * @param  array<int, string>  $abilities
     */
    private function tokenResponse(User $user, string $tokenName, array $abilities, ?Parish $parish = null): JsonResponse
    {
        $token = $user->createToken($tokenName, $abilities);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'abilities' => $abilities,
            'user' => $this->userPayload($user),
            'parish' => $parish ? $this->parishPayload($parish) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'system_role' => $user->system_role->value,
            'parishes' => $user->relationLoaded('parishes')
                ? $user->parishes->map(fn (Parish $parish) => $this->parishPayload($parish))->values()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parishPayload(Parish $parish): array
    {
        return [
            'id' => $parish->id,
            'name' => $parish->name,
            'slug' => $parish->slug,
            'cnpj' => $parish->cnpj,
            'active' => $parish->active,
        ];
    }
}
