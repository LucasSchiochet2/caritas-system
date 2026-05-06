<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParishRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', Rule::unique('parishes', 'cnpj')->ignore($id)],
            'active' => ['boolean'],
        ];
    }
}
