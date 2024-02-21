<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @bodyParam is_public int Whether the travel can be seen by public. Enum: 0:false, 1:true. Example: 1
 * @bodyParam name string required The name of the travel. Example: Travel One
 * @bodyParam description string required. Example: Travel description
 * @bodyParam number_of_days int required. Example: 4
 */
class UpdateTravelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_public' => 'boolean',
            'name' => ['required', Rule::unique('travels', 'name')->ignore($this->travel)],
            'description' => 'required',
            'number_of_days' => ['required', 'integer'],
        ];
    }
}
