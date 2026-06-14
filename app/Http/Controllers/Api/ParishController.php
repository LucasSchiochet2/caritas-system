<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parish;
use App\Models\Cashbox;
use App\Models\ParishInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParishController extends Controller
{
    public function index(): JsonResponse
    {
        $parishes = Parish::query()
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Parish $parish) => $this->payload($parish));

        return response()->json(['data' => $parishes]);
    }
    public function inactive_parishes(): JsonResponse
    {
        $this->authorizeDioceseAdmin(request());
        $parishes = Parish::query()
            ->where('active', false)
            ->orderBy('name')
            ->get()
            ->map(fn (Parish $parish) => $this->payload($parish));

        return response()->json(['data' => $parishes]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', Rule::unique('parishes', 'cnpj')],
            'active' => ['sometimes', 'boolean'],
        ]);

        $parish = Parish::query()->create([
            'name' => $data['name'],
            'cnpj' => $data['cnpj'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        Cashbox::query()->create([
            'parish_id' => $parish->id,
            'name' => 'Caixa Principal',
            'balance' => 0,
        ]);
        
        ParishInventory::query()->create([
            'parish_id' => $parish->id,
            'name' => 'Inventário Principal',
            'description' => null,
        ]);

        return response()->json(['data' => $this->payload($parish)], 201);
    }

    public function update(Request $request, Parish $parish): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', Rule::unique('parishes', 'cnpj')->ignore($parish)],
            'active' => ['sometimes', 'boolean'],
        ]);

        $parish->fill($data);
        $parish->save();

        return response()->json(['data' => $this->payload($parish)]);
    }

    public function destroy(Request $request, Parish $parish): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $parish->delete();

        return response()->json(null, 204);
    }

    public function activate(Request $request, Parish $parish): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $parish->active = true;
        $parish->save();

        return response()->json(['data' => $this->payload($parish)]);
    }

    private function authorizeDioceseAdmin(Request $request): void
    {
        abort_unless($request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese'), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Parish $parish): array
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
