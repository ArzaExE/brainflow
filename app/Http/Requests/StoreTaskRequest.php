<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'priority'       => ['required', Rule::in(['low', 'medium', 'high'])],
            'column_id'      => ['required', 'integer', Rule::exists('project_columns', 'id')
                ->where('project_id', $this->route('project')->id)],
            'due_date'       => ['nullable', 'date', 'after:today'],
            'assignee_ids'   => ['required', 'array', 'min:1'],
            'assignee_ids.*' => ['integer', Rule::exists('users', 'id')],
            'label_ids'      => ['nullable', 'array'],
            'label_ids.*'    => ['integer', Rule::exists('labels', 'id')],
            'subtasks'       => ['nullable', 'array'],
            'subtasks.*.title' => ['required_with:subtasks', 'string', 'max:255'],
            'subtasks.*.done'  => ['boolean'],
        ];
    }

    public function messages(): array{
        return [
            'due_date.after' => 'Il campo scadenza deve essere una data successiva a oggi.',
        ];
    }
}
