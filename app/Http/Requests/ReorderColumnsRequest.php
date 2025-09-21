<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Board;

class ReorderColumnsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $board = $this->route('board');
        return $board && $this->user()->can('update', $board);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:board_columns,id',
            'columns.*.position' => 'required|integer|min:0',
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
            'columns.required' => 'Column data is required.',
            'columns.array' => 'Column data must be an array.',
            'columns.*.id.required' => 'Column ID is required.',
            'columns.*.id.exists' => 'One or more columns are invalid.',
            'columns.*.position.required' => 'Column position is required.',
            'columns.*.position.integer' => 'Column position must be a number.',
            'columns.*.position.min' => 'Column position must be at least 0.',
        ];
    }
}
