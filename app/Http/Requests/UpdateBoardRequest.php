<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed,archived',
            'columns' => 'nullable|array|min:1',
            'columns.*.id' => 'nullable|exists:board_columns,id',
            'columns.*.name' => 'required|string|max:255',
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
            'name.required' => 'The board name is required.',
            'name.max' => 'The board name may not be greater than 255 characters.',
            'description.max' => 'The board description may not be greater than 1000 characters.',
            'status.required' => 'The board status is required.',
            'status.in' => 'The board status must be active, completed, or archived.',
            'columns.min' => 'At least one column is required.',
            'columns.*.id.exists' => 'One or more columns are invalid.',
            'columns.*.name.required' => 'Column name is required.',
            'columns.*.name.max' => 'Column name may not be greater than 255 characters.',
            'columns.*.position.required' => 'Column position is required.',
            'columns.*.position.integer' => 'Column position must be a number.',
            'columns.*.position.min' => 'Column position must be at least 0.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $this->has('columns')) {
                return;
            }

            $board = $this->route('board');
            $columns = $this->input('columns', []);

            // Check for duplicate column names
            $names = array_map(fn ($col) => strtolower(trim($col['name'])), $columns);
            if (count($names) !== count(array_unique($names))) {
                $validator->errors()->add('columns', 'Column names must be unique.');
            }

            // Check if trying to delete columns with cards
            $existingColumnIds = array_filter(array_column($columns, 'id'));

            // Get columns that will be deleted (not in the new list)
            if (! empty($existingColumnIds)) {
                $deletedColumns = $board->columns()
                    ->whereNotIn('id', $existingColumnIds)
                    ->withCount('cards')
                    ->get();
            } else {
                // If no existing IDs, all columns are being replaced
                $deletedColumns = $board->columns()->withCount('cards')->get();
            }

            foreach ($deletedColumns as $column) {
                if ($column->cards_count > 0) {
                    $validator->errors()->add(
                        'columns',
                        "Cannot delete column '{$column->name}' because it contains {$column->cards_count} card(s). Please move or delete the cards first."
                    );
                }
            }

            // Validate that columns belong to this board
            if (! empty($existingColumnIds)) {
                $validColumns = $board->columns()->whereIn('id', $existingColumnIds)->count();
                if ($validColumns !== count($existingColumnIds)) {
                    $validator->errors()->add('columns', 'One or more columns do not belong to this board.');
                }
            }
        });
    }
}
