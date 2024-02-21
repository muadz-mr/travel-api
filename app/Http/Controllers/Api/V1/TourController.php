<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Tour management
 *
 * APIs for managing tours
 */
class TourController extends Controller
{
    /**
     * Retrieve tours for a specific travel
     *
     * This endpoint allows you to retrieve paginated tours record for a specific travel.
     *
     * @urlParam travel_slug string required The slug of the travel. Example: Travel to the West
     *
     * @queryParam priceFrom Lower price limit. Example: 10.50
     * @queryParam priceTo Upper price limit. Example: 99.99
     * @queryParam dateFrom Start date limit. Example: 2024-01-01
     * @queryParam dateTo End date limit. Example: 2024-01-10
     * @queryParam sortBy Paramater to sort the records by. Enum: price. Example: price
     * @queryParam sortOrder Sort order for the sort parameter. Enum: asc, desc. Example: asc
     *
     * @apiResourceCollection App\Http\Resources\TourResource
     *
     * @apiResourceModel App\Models\Tour
     **/
    public function index(Travel $travel, Request $request)
    {
        $validated = $request->validate(
            [
                'priceFrom' => 'numeric',
                'priceTo' => 'numeric|gte:priceFrom',
                'dateFrom' => 'date',
                'dateTo' => 'date|after_or_equal:dateFrom',
                'sortBy' => Rule::in(['price']),
                'sortOrder' => Rule::in(['asc', 'desc']),
            ],
            [
                'sortBy.in' => "The 'sortBy' parameter accepts only 'price' value.",
                'sortOrder.in' => "The 'sortOrder' parameter accepts only 'asc' or 'desc' value.",
            ]
        );

        $tours = $travel->tours()
            ->when(isset($validated['priceFrom']), function ($query) use ($validated) {
                $query->where('price', '>=', $validated['priceFrom'] * 100);
            })
            ->when(isset($validated['priceTo']), function ($query) use ($validated) {
                $query->where('price', '<=', $validated['priceTo'] * 100);
            })
            ->when(isset($validated['dateFrom']), function ($query) use ($validated) {
                $query->where('starting_date', '>=', $validated['dateFrom']);
            })
            ->when(isset($validated['dateTo']), function ($query) use ($validated) {
                $query->where('starting_date', '<=', $validated['dateTo']);
            })
            ->when(isset($validated['sortBy']) && isset($validated['sortOrder']), function ($query) use ($validated) {
                // If to skip sorting if invalid value present
                // if (!in_array(isset($validated['sortOrder']), ['asc', 'desc'])) return;
                $query->orderBy($validated['sortBy'], $validated['sortOrder']);
            })
            ->orderBy('starting_date')
            ->paginate();

        return TourResource::collection($tours);
    }
}
