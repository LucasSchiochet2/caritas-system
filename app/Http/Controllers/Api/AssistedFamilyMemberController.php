<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistedFamilyMember;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistedFamilyMemberController extends Controller
{
    public function index(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $members = $family->assistedFamilyMembers()
            ->orderBy('mother_name')
            ->get()
            ->map(fn (AssistedFamilyMember $member) => $this->payload($member));

        return response()->json(['data' => $members]);
    }

    public function store(Request $request, Family $family): JsonResponse
    {
        $this->authorizeFamily($request, $family);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:50'],
            'age' => ['required', 'integer', 'min:0', 'max:130'],
            'registration_status' => ['required', 'string', 'max:100'],
            'registration_date' => ['required', 'date'],
            'personal_income' => ['required', 'numeric', 'min:0'],
        ]);

        $member = $family->assistedFamilyMembers()->create([
            'parish_id' => $family->parish_id,
            'name' => $data['name'],
            'mother_name' => $data['mother_name'],
            'relationship' => $data['relationship'],
            'age' => $data['age'],
            'registration_status' => $data['registration_status'],
            'registration_date' => $data['registration_date'],
            'personal_income' => $data['personal_income'],
        ]);

        return response()->json(['data' => $this->payload($member)], 201);
    }

    public function update(Request $request, AssistedFamilyMember $assistedFamilyMember): JsonResponse
    {
        $this->authorizeMember($request, $assistedFamilyMember);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'mother_name' => ['sometimes', 'required', 'string', 'max:255'],
            'relationship' => ['sometimes', 'required', 'string', 'max:50'],
            'age' => ['sometimes', 'required', 'integer', 'min:0', 'max:130'],
            'registration_status' => ['sometimes', 'required', 'string', 'max:100'],
            'registration_date' => ['sometimes', 'required', 'date'],
            'personal_income' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $assistedFamilyMember->fill($data);
        $assistedFamilyMember->save();

        if ($assistedFamilyMember->is_responsible && $assistedFamilyMember->wasChanged('name')) {
            $assistedFamilyMember->family()->update(['name' => $assistedFamilyMember->name]);
        }

        return response()->json(['data' => $this->payload($assistedFamilyMember)]);
    }

    public function destroy(Request $request, AssistedFamilyMember $assistedFamilyMember): JsonResponse
    {
        $this->authorizeMember($request, $assistedFamilyMember);
        abort_if($assistedFamilyMember->is_responsible, 422);

        $assistedFamilyMember->delete();

        return response()->json(null, 204);
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

    private function authorizeMember(Request $request, AssistedFamilyMember $member): void
    {
        if ($this->isDioceseScope($request)) {
            return;
        }

        $parishScopeId = $this->parishScopeId($request);

        abort_unless(
            $parishScopeId !== null
                && $member->parish_id === $parishScopeId
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
    private function payload(AssistedFamilyMember $member): array
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
