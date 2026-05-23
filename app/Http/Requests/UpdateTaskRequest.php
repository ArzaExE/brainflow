<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title'            => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'nullable', 'string'],
            'priority'         => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'column_id'        => ['sometimes', 'integer', 'exists:project_columns,id'],
            'due_date'         => ['sometimes', 'nullable', 'date'],
            'assignee_ids'     => ['sometimes', 'array', 'min:1'],
            'assignee_ids.*'   => ['integer', 'exists:users,id'],
            'label_ids'        => ['sometimes', 'array'],
            'label_ids.*'      => ['integer', 'exists:labels,id'],
            'subtasks'         => ['sometimes', 'array'],
            'subtasks.*.id'    => ['nullable', 'integer'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
            'subtasks.*.done'  => ['boolean'],
        ];
    }
}
