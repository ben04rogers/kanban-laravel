<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $card = $this->route('card');

        return $card && $this->user()->can('update', $card);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'board_column_id' => 'required|exists:board_columns,id',
            'position' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'board_column_id.required' => 'Please select a column.',
            'board_column_id.exists' => 'The selected column is invalid.',
            'position.required' => 'Position is required.',
            'position.integer' => 'Position must be a number.',
            'position.min' => 'Position must be at least 0.',
        ];
    }
}
