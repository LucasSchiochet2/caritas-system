<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogsCashbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class LogsCashboxController extends Controller
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
        $items = LogsCashbox::query()
            ->when($parishScopeId !== null, function ($query) use ($parishScopeId) {
                $query->whereHas('cashbox', fn ($cashboxQuery) => $cashboxQuery->where('parish_id', $parishScopeId));
            })
            ->get()
            ->map(fn (LogsCashbox $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }
    public function destroy(Request $request, LogsCashbox $logsCashbox): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
            abort_unless($logsCashbox->cashbox?->parish_id === $parishScopeId, 403);
        }

        $logsCashbox->delete();

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
    private function payload(LogsCashbox $item): array
    {
        return [
            'id' => $item->id,
            'cashbox_id' => $item->cashbox_id,
            'user_id' => $item->user_id,
            'movement_type' => $item->movement_type,
            'reason' => $item->reason,
            'amount' => $item->amount,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
