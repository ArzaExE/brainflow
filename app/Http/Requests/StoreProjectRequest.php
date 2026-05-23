<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name' => 'required|string|max:32|unique:projects,name',
            'description' => 'nullable|string|max:255',
            'priority' => 'required|string|in:low,medium,high',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del progetto è obbligatorio.',
            'name.string'   => 'Il nome del progetto deve essere una stringa di testo.',
            'name.max'      => 'Il nome del progetto non può superare i :max caratteri.',

            'description.string' => 'La descrizione deve essere una stringa di testo.',
            'description.max'    => 'La descrizione non può superare i :max caratteri.',

            'priority.required' => 'La priorità è obbligatoria.',
            'priority.string'   => 'La priorità deve essere una stringa.',
            'priority.in'       => 'La priorità deve essere bassa, media o alta.',
        ];
    }
}
