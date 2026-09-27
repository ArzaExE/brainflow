<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name'        => 'required|string|max:32|unique:projects,name',
            'description' => 'nullable|string|max:255',
            'priority'    => 'required|string|in:low,medium,high',
        ];
    }

    public function messages(): array
    {
        return [
            'priority.in' => 'La priorità deve essere bassa, media o alta.',
        ];
    }
}
