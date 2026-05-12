<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BazaarCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BazaarCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $customers = BazaarCustomer::query()
            ->orderBy('name')
            ->get()
            ->map(fn (BazaarCustomer $customer) => $this->payload($customer));

        return response()->json(['data' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('bazaar_customers', 'cpf')],
        ]);

        $customer = BazaarCustomer::query()->create($data);

        return response()->json(['data' => $this->payload($customer)], 201);
    }

    public function update(Request $request, BazaarCustomer $bazaarCustomer): JsonResponse
    {
        $this->authorizeDioceseAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'cpf' => ['sometimes', 'required', 'string', 'max:14', Rule::unique('bazaar_customers', 'cpf')->ignore($bazaarCustomer)],
        ]);

        $bazaarCustomer->fill($data);
        $bazaarCustomer->save();

        return response()->json(['data' => $this->payload($bazaarCustomer)]);
    }

    private function authorizeDioceseAdmin(Request $request): void
    {
        abort_unless($request->user()->isDioceseAdmin() && $request->user()->tokenCan('diocese'), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(BazaarCustomer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'birth_date' => $customer->birth_date?->toDateString(),
            'cpf' => $customer->cpf,
        ];
    }
}
