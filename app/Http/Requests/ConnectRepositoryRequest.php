<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConnectRepositoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy check (board owner) happens in the controller.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'access_token' => 'required|string|min:10',
            'repository' => ['required', 'string', 'regex:/^[\w.-]+\/[\w.-]+$/'],
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
            'access_token.required' => 'A GitHub access token is required.',
            'repository.required' => 'The repository is required.',
            'repository.regex' => 'Enter the repository as owner/repo, e.g. acme/website.',
        ];
    }
}
