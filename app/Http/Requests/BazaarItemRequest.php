<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BazaarItemRequest extends FormRequest
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
        return [
            'suggested_price' => ['required', 'numeric', 'min:0'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'string', 'max:100'],
        ];
    }
}