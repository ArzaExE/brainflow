<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-ZÀ-ÿ]+(\s[a-zA-ZÀ-ÿ]+)?$/'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
                Rule::unique(User::class)
                    ->whereNull('deleted_at'),
            ],
            'sys_role' => ['required', Rule::in('admin', 'user')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Il nome può contenere solo lettere e al massimo uno spazio.',
            'email.unique'       => 'Esiste già un account con questa email.',
            'sys_role.*'       => 'Seleziona un ruolo tra admin o user.',
        ];
    }
}
