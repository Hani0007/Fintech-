<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsGoalRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'target_amount' => 'sometimes|required|numeric|min:0',
            'saved_amount' => 'sometimes|nullable|numeric|min:0',
            'deadline' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|in:active,completed,cancelled',
        ];
    }
}
