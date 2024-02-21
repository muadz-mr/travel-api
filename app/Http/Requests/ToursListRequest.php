<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToursListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'priceFrom' => 'numeric',
            'priceTo' => 'numeric|gte:priceFrom',
            'dateFrom' => 'date',
            'dateTo' => 'date|after_or_equal:dateFrom',
            'sortBy' => Rule::in(['price']),
            'sortOrder' => Rule::in(['asc', 'desc']),
        ];
    }

    public function messages(): array
    {
        return [
            'sortBy.in' => "The 'sortBy' parameter accepts only 'price' value.",
            'sortOrder.in' => "The 'sortOrder' parameter accepts only 'asc' or 'desc' value.",
        ];
    }
}
