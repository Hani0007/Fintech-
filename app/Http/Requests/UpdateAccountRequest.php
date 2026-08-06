<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'account_type' => 'sometimes|required|in:cash,bank,easypaisa,jazzcash,credit_card',
            'balance' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|in:USD,PKR,AED,EUR',
        ];
    }
}
