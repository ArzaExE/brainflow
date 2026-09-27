<?php

namespace App\Http\Requests;

use App\Models\ColumnType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectColumnRequest extends FormRequest
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
        $validIds = ColumnType::select('id')->get()->map(function ($m){
            $arr = $m->toArray();
            return $arr['id'];
        });

        $ids = $validIds->toArray();

        return [
            'column_type_id' => ['required', 'string', Rule::in($validIds)]
        ];
    }
}
