<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\HomeVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeVisitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parishScopeId = $this->authorizedParishScopeId($request);

        $items = HomeVisit::query()
            ->when($parishScopeId !== null, function ($query) use ($parishScopeId) {
                $query
                    ->whereHas('family', fn ($familyQuery) => $familyQuery->where('parish_id', $parishScopeId))
                    ->where('status', '!=', 'cancelled')
                    ->where('visit_date', '>=', now()->subMonths(2));
            })
            ->orderBy('visit_date')
            ->get()
            ->map(fn (HomeVisit $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function history(Request $request): JsonResponse
    {
        $parishScopeId = $this->authorizedParishScopeId($request);

        $items = HomeVisit::query()
            ->when($parishScopeId !== null, function ($query) use ($parishScopeId) {
                $query->whereHas('family', fn ($familyQuery) => $familyQuery->where('parish_id', $parishScopeId));
            })
            ->orderByDesc('visit_date')
            ->get()
            ->map(fn (HomeVisit $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function indexByFamily(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $items = $family->homeVisits()
            ->orderByDesc('visit_date')
            ->get()
            ->map(fn (HomeVisit $item) => $this->payload($item));

        return response()->json(['data' => $items]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
        ]);

        $item = $family->homeVisits()->create($data);

        return response()->json(['data' => $this->payload($item)], 201);
    }

    public function update(Request $request, HomeVisit $homeVisit): JsonResponse
    {
        $this->authorizeHomeVisit($request, $homeVisit);

        $data = $request->validate([
            'visit_date' => ['sometimes', 'required', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1500'],
            'forwarding' => ['sometimes', 'nullable', 'string', 'max:500'],
            'next_visit_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $homeVisit->update($data);

        return response()->json(['data' => $this->payload($homeVisit)]);
    }

    public function destroy(Request $request, HomeVisit $homeVisit): JsonResponse
    {
        $this->authorizeHomeVisit($request, $homeVisit);

        $homeVisit->delete();

        return response()->json(null, 204);
    }

    public function reschedule(Request $request, HomeVisit $homeVisit): JsonResponse
    {
        $this->authorizeHomeVisit($request, $homeVisit);

        $data = $request->validate([
            'visit_date' => ['required', 'date'],
        ]);

        $homeVisit->update($data);

        return response()->json(['data' => $this->payload($homeVisit)]);
    }
    public function cancel(Request $request, HomeVisit $homeVisit): JsonResponse
    {
        $this->authorizeHomeVisit($request, $homeVisit);

        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
        ]);
        $data['status'] = 'cancelled';
        $homeVisit->update($data);

        return response()->json(['data' => $this->payload($homeVisit)]);
    }

    public function visit_record(Request $request, HomeVisit $homeVisit): JsonResponse
    {
        $this->authorizeHomeVisit($request, $homeVisit);

        $data = $request->validate([
            'notes' => ['sometimes', 'nullable', 'string', 'max:1500'],
            'forwarding' => ['sometimes', 'nullable', 'string', 'max:500'],
            'next_visit_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $homeVisit->update($data);

        if ($data['next_visit_date'] ?? false) {
            HomeVisit::query()->create([
                'family_id' => $homeVisit->family_id,
                'user_id' => $homeVisit->user_id,
                'visit_date' => $data['next_visit_date'],
            ]);
        }

        return response()->json(['data' => $this->payload($homeVisit)]);
    }

    private function authorizedParishScopeId(Request $request): ?int
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);

        abort_unless($isDioceseScope || $parishScopeId !== null, 403);

        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        return $parishScopeId;
    }

    private function authorizeFamily(Request $request, Family $family): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $family->parish_id === $parishScopeId
                && $request->user()->canManageParish($parishScopeId),
            403
        );
    }

    private function authorizeHomeVisit(Request $request, HomeVisit $homeVisit): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $homeVisit->family?->parish_id === $parishScopeId
                && $request->user()->canManageParish($parishScopeId),
            403
        );
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
    private function payload(HomeVisit $item): array
    {
        return [
            'id' => $item->id,
            'family_id' => $item->family_id,
            'user_id' => $item->user_id,
            'visit_date' => $item->visit_date?->toDateTimeString(),
            'notes' => $item->notes,
            'forwarding' => $item->forwarding,
            'next_visit_date' => $item->next_visit_date?->toDateTimeString(),
            'status' => $item->status,
        ];
    }
}
