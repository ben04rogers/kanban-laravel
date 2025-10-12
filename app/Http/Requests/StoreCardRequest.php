<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class StoreCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user can view the board
        $board = Board::find($this->board_id);

        return $board && $this->user()->can('view', $board);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->assigned_user_id) {
                $board = Board::find($this->board_id);
                if ($board) {
                    // Check if the assigned user has access to the board
                    $hasAccess = $board->user_id === $this->assigned_user_id ||
                                $board->shares()->where('user_id', $this->assigned_user_id)->exists();

                    if (! $hasAccess) {
                        $validator->errors()->add('assigned_user_id', 'The selected user does not have access to this board.');
                    }
                }
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:50000',
            'board_id' => 'required|exists:boards,id',
            'board_column_id' => 'required|exists:board_columns,id',
            'assigned_user_id' => 'nullable|exists:users,id',
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
            'title.required' => 'The card title is required.',
            'title.max' => 'The card title may not be greater than 255 characters.',
            'description.max' => 'The card description may not be greater than 50000 characters.',
            'board_id.required' => 'Please select a board.',
            'board_id.exists' => 'The selected board is invalid.',
            'board_column_id.required' => 'Please select a column.',
            'board_column_id.exists' => 'The selected column is invalid.',
        ];
    }
}
