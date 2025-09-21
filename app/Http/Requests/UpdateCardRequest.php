<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Card;
use App\Models\Board;

class UpdateCardRequest extends FormRequest
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
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->assigned_user_id) {
                $card = $this->route('card');
                if ($card) {
                    $board = $card->board;
                    // Check if the assigned user has access to the board
                    $hasAccess = $board->user_id === $this->assigned_user_id || 
                                $board->shares()->where('user_id', $this->assigned_user_id)->exists();
                    
                    if (!$hasAccess) {
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
        ];
    }
}
