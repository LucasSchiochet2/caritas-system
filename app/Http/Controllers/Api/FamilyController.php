<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistedFamilyMember;
use App\Models\Family;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyController extends Controller
{
    private const SIMILARITY_THRESHOLD = 0.7;

    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        $listAllParishes = $request->boolean('all');
        $search = trim((string) $request->query('search', ''));

        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        // abort_if($listAllParishes && ! $isDioceseScope, 403);

        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $ownParishIds = $parishScopeId !== null
            ? [$parishScopeId]
            : $actor->administeredParishes()->pluck('parishes.id')->all();

        $families = Family::query()
            ->with(['parish:id,name,slug,active', 'responsible', 'assistedFamilyMembers'])
            ->where('is_active', true)
            ->when(! $listAllParishes, fn ($query) => $query->whereIn('parish_id', $ownParishIds))
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('name')
            ->get()
            ->map(fn (Family $family) => $this->payload($family));

        return response()->json(['data' => $families]);
    }

    public function inactivateFamilies(Request $request): JsonResponse
    {
        $actor = $request->user();
        $parishScopeId = $this->parishScopeId($request);
        $isDioceseScope = $this->isDioceseScope($request);
        $listAllParishes = $request->boolean('all');
        $search = trim((string) $request->query('search', ''));

        abort_unless($isDioceseScope || $parishScopeId !== null, 403);
        // abort_if($listAllParishes && ! $isDioceseScope, 403);

        if ($parishScopeId !== null) {
            abort_unless($actor->canManageParish($parishScopeId), 403);
        }

        $ownParishIds = $parishScopeId !== null
            ? [$parishScopeId]
            : $actor->administeredParishes()->pluck('parishes.id')->all();

        $families = Family::query()
            ->with(['parish:id,name,slug,active', 'responsible', 'assistedFamilyMembers'])
            ->where('is_active', false)
            ->when(! $listAllParishes, fn ($query) => $query->whereIn('parish_id', $ownParishIds))
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('name')
            ->get()
            ->map(fn (Family $family) => $this->payload($family));

        return response()->json(['data' => $families]);
    }

    public function store(Request $request): JsonResponse
    {
        $isDioceseScope = $this->isDioceseScope($request);

        $data = $request->validate([
            'parish_id' => [$isDioceseScope ? 'required' : 'sometimes', 'integer', 'exists:parishes,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
            'responsible' => ['required', 'array'],
            'responsible.name' => ['required', 'string', 'max:255'],
            'responsible.mother_name' => ['required', 'string', 'max:255'],
            'responsible.relationship' => ['required', 'string', 'max:50'],
            'responsible.age' => ['required', 'integer', 'min:0', 'max:130'],
            'responsible.registration_status' => ['required', 'string', 'max:100'],
            'responsible.registration_date' => ['required', 'date'],
            'responsible.personal_income' => ['required', 'numeric', 'min:0'],
        ]);

        $parishId = $this->writableParishId($request, $data['parish_id'] ?? null);

        $family = DB::transaction(function () use ($data, $parishId) {
            $family = Family::query()->create([
                'parish_id' => $parishId,
                'name' => $data['responsible']['name'],
                'address' => $data['address'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);

            $family->assistedFamilyMembers()->create([
                'parish_id' => $parishId,
                'name' => $data['responsible']['name'],
                'mother_name' => $data['responsible']['mother_name'],
                'relationship' => $data['responsible']['relationship'],
                'age' => $data['responsible']['age'],
                'registration_status' => $data['responsible']['registration_status'],
                'registration_date' => $data['responsible']['registration_date'],
                'personal_income' => $data['responsible']['personal_income'],
                'is_responsible' => true,
            ]);

            return $family;
        });

        $family->load(['parish:id,name,slug,active', 'responsible', 'assistedFamilyMembers']);

        return response()->json(['data' => $this->payload($family)], 201);
    }

    public function update(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $data = $request->validate([
            'parish_id' => ['sometimes', 'required', 'integer', 'exists:parishes,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
        ]);

        if (array_key_exists('parish_id', $data)) {
            abort_unless($this->isDioceseScope($request), 403);
            $family->parish_id = $data['parish_id'];
        }

        $family->fill(collect($data)->only(['address', 'observations'])->all());
        $family->save();

        if ($family->wasChanged('parish_id')) {
            $family->assistedFamilyMembers()->update(['parish_id' => $family->parish_id]);
        }

        $family->load(['parish:id,name,slug,active', 'responsible', 'assistedFamilyMembers']);

        return response()->json(['data' => $this->payload($family)]);
    }

    public function destroy(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $family->delete();

        return response()->json(null, 204);
    }

    public function inactivate(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $family->is_active = false;
        $family->save();

        return response()->json(null, 204);
    }

    public function activate(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $family->is_active = true;
        $family->save();

        return response()->json(null, 204);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $like = '%'.$search.'%';

        $query->where(function (Builder $query) use ($search, $like) {
            $query
                ->where('name', 'like', $like)
                ->orWhere('address', 'like', $like)
                ->orWhere('observations', 'like', $like)
                ->orWhereHas('parish', function (Builder $query) use ($search, $like) {
                    $query->where('name', 'like', $like);
                    $this->orWhereSimilarity($query, 'name', $search);
                })
                ->orWhereHas('assistedFamilyMembers', function (Builder $query) use ($search, $like) {
                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('mother_name', 'like', $like);

                    $this->orWhereSimilarity($query, 'name', $search);
                    $this->orWhereSimilarity($query, 'mother_name', $search);
                });

            $this->orWhereSimilarity($query, 'name', $search);
            $this->orWhereSimilarity($query, 'address', $search);
            $this->orWhereSimilarity($query, 'observations', $search);
        });
    }

    private function orWhereSimilarity(Builder $query, string $column, string $search): void
    {
        if (! $this->usesPostgres()) {
            return;
        }

        $query->orWhereRaw(
            "similarity(coalesce({$column}, ''), ?) > ?",
            [$search, self::SIMILARITY_THRESHOLD]
        );
    }

    private function usesPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    private function writableParishId(Request $request, ?int $requestedParishId): int
    {
        if ($this->isDioceseScope($request)) {
            return (int) $requestedParishId;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless($parishScopeId !== null && $request->user()->canManageParish($parishScopeId), 403);
        abort_if($request->has('parish_id') && (int) $requestedParishId !== $parishScopeId, 403);

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
    private function payload(Family $family): array
    {
        return [
            'id' => $family->id,
            'parish_id' => $family->parish_id,
            'name' => $family->name,
            'address' => $family->address,
            'observations' => $family->observations,
            'is_active' => $family->is_active,
            'parish' => $family->relationLoaded('parish') && $family->parish
                ? [
                    'id' => $family->parish->id,
                    'name' => $family->parish->name,
                    'slug' => $family->parish->slug,
                    'active' => $family->parish->active,
                ]
                : null,
            'responsible' => $family->relationLoaded('responsible') && $family->responsible
                ? $this->memberPayload($family->responsible)
                : null,
            'assisted_family_members' => $family->relationLoaded('assistedFamilyMembers')
                ? $family->assistedFamilyMembers
                    ->map(fn (AssistedFamilyMember $member) => $this->memberPayload($member))
                    ->values()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(AssistedFamilyMember $member): array
    {
        return [
            'id' => $member->id,
            'parish_id' => $member->parish_id,
            'family_id' => $member->family_id,
            'name' => $member->name,
            'mother_name' => $member->mother_name,
            'relationship' => $member->relationship,
            'age' => $member->age,
            'registration_status' => $member->registration_status,
            'registration_date' => $member->registration_date?->toDateString(),
            'personal_income' => $member->personal_income,
            'is_responsible' => $member->is_responsible,
        ];
    }
}
