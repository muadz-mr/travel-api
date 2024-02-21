<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the tour. Example: Tour Five
 * @bodyParam starting_date string required The start date of the tour. Example: 2024-01-01
 * @bodyParam ending_date string required The end date of the tour. Example: 2024-01-10
 * @bodyParam price number required The price of the tour. Example: 2024-01-10
 */
class TourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'starting_date' => ['required', 'date'],
            'ending_date' => ['required', 'date', 'after:starting_date'],
            'price' => ['required', 'numeric'],
        ];
    }
}
