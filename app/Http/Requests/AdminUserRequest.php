<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_user()?->isDioceseAdmin() === true
            || backpack_user()?->isParishAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('id');
        $passwordRules = $this->isMethod('post')
            ? ['required', 'string', 'min:8']
            : ['nullable', 'string', 'min:8'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => $passwordRules,
            'system_role' => ['required', Rule::enum(UserRole::class)],
            'parishes' => ['nullable', 'array'],
            'parishes.*' => ['integer', 'exists:parishes,id'],
        ];
    }
}
