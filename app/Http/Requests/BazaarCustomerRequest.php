<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BazaarCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_user()?->isDioceseAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customerId = $this->route('id') ?? $this->route('bazaar_customer') ?? $this->route('bazaar-customer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('bazaar_customers', 'cpf')->ignore($customerId)],
        ];
    }
}
