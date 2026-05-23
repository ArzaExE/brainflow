<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Regex: accetta solo lettere e uno spazio seguto da altre lettere
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-ZÀ-ÿ]+(\s[a-zA-ZÀ-ÿ]+)?$/'],
            'email' => ['required',
                'string',
                'lowercase',
                app()->environment('testing')
                    ? 'email:rfc'
                    : 'email:rfc,dns',
                'max:255',
                'unique:'.User::class
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
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
            'name.regex' => 'Il nome può contenere solo lettere e al massimo uno spazio.',
            'email.unique'       => 'Esiste già un account con questa email.',
            'password.confirmed' => 'Le password non corrispondono.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'     => 'nome',
            'email'    => 'email',
            'password' => 'password',
        ];
    }
}
