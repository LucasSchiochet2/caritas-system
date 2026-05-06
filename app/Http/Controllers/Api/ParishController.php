<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parish;
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

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese'), 403);

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

        return response()->json(['data' => $this->payload($parish)], 201);
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
