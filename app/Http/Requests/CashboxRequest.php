<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashboxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return backpack_user()?->isParishAdmin() === true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
{
    // Obrigatório APENAS SE a movimentação (amount) NÃO for enviada.
    // Em PUT/PATCH, ele se torna opcional ('sometimes').
    $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
    
    $balanceRule = $isUpdate 
        ? 'sometimes|numeric|min:0' 
        : 'required_without:amount|numeric|min:0';

    // Obrigatório APENAS SE o balanço (balance) NÃO for enviado.
    $amountRule = $isUpdate
        ? 'sometimes|numeric|min:0.01'
        : 'required_without:balance|numeric|min:0.01';

    // Obrigatório se o valor da movimentação (amount) for enviado.
    $movementTypeRule = 'required_with:amount|in:in,out';

    // Obrigatório APENAS se o tipo de movimentação for 'out' (saída)
    $reasonRule = $this->input('movement_type') === 'out'
        ? 'required|string|max:100'
        : 'nullable|string|max:100';

    return [
        'name'          => 'required|min:3|max:255',
        'balance'       => $balanceRule,
        'amount'        => $amountRule,
        'movement_type' => $movementTypeRule,
        'reason'        => $reasonRule,
    ];
}

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
