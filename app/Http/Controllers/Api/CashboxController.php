<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashbox;
use App\Models\LogsCashbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class CashboxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }
        $items = Cashbox::query()
            ->when($parishScopeId !== null, fn ($query) => $query->where('parish_id', $parishScopeId))
            ->get()
            ->map(fn (Cashbox $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $data = $request->validate([
            'parish_id' => [$isDioceseScope ? 'required' : 'prohibited', 'integer', 'exists:parishes,id'],
            'name' => 'required|min:3|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        $data['parish_id'] = $parishScopeId ?? $data['parish_id'];

        $item = Cashbox::query()->create($data);

        return response()->json(['data' => $this->payload($item)], 201);
    }

    public function update(Request $request, Cashbox $cashbox): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($cashbox->parish_id === $parishScopeId, 403);
        }
        $balanceRule = 'sometimes|numeric|min:0';
        $amountRule = 'sometimes|numeric|min:0.01';
        $movementTypeRule = 'required_with:amount|in:in,out';
        $reasonRule = $request->input('movement_type') === 'out'
            ? 'required|string|max:100'
            : 'nullable|string|max:100';
         $data = $request->validate([
            'name'          => 'required|min:3|max:255',
            'balance'       => $balanceRule,
            'amount'        => $amountRule,
            'movement_type' => $movementTypeRule,
            'reason'        => $reasonRule,
        ]);

        if ($request->has('amount')) {
            $currentBalance = $cashbox->balance; // Pega o valor atual do banco
            
            $data['balance'] = $request->input('movement_type') === 'out'
                ? $currentBalance - $request->input('amount')
                : $currentBalance + $request->input('amount');
                
            // Garante que o saldo não fique negativo caso sua regra de negócio não permita
            if ($data['balance'] < 0) {
                return response()->json(['message' => 'Saldo insuficiente para realizar esta saída.'], 422);
            }
        }

        LogsCashbox::query()->create([
            'cashbox_id' => $cashbox->id,
            'user_id' => $actor->id,
            'movement_type' => $data['movement_type'] ?? 'update',
            'reason' => $data['reason'] ?? null,
            'amount' => $request->input('amount') ?? 0,
        ]);

        $dataToSave = array_intersect_key($data, array_flip(['name', 'balance']));
        $cashbox->fill($dataToSave);
        $cashbox->save();

        return response()->json(['data' => $this->payload($cashbox)]);
    }

    public function destroy(Request $request, Cashbox $cashbox): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($cashbox->parish_id === $parishScopeId, 403);
        }

        $cashbox->delete();

        return response()->json(null, 204);
    }
     private function isDioceseScope(Request $request): bool
    {
        return $request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese');
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
    private function payload(Cashbox $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'balance' => $item->balance,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
