<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToursListRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

class TourController extends Controller
{
    public function index(Travel $travel, ToursListRequest $request)
    {
        $validated = $request->validated();

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
            ->orderBy('starting_date',)
            ->paginate();

        return TourResource::collection($tours);
    }
}
