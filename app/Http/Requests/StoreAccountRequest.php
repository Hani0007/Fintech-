<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:cash,bank,easypaisa,jazzcash,credit_card',
            'balance' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,PKR,AED,EUR',
            'account_number' => 'required|string|max:50',
        ];
    }
}
