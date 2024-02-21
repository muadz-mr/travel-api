<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

/**
 * @group Tour management
 *
 * APIs for managing tours
 */
class TourController extends Controller
{
    /**
     * Create tour for a specific travel
     *
     * This endpoint allows you to add new tour for a specific travel.
     *
     * @authenticated
     *
     * @urlParam travel_id string required The ID of the travel. Example: hd3h-dh8h3-2b23ji
     **/
    public function store(Travel $travel, TourRequest $request)
    {
        $tour = $travel->tours()->create($request->validated());

        return new TourResource($tour);
    }
}
