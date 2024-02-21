<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelRequest;
use App\Http\Requests\UpdateTravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;

/**
 * @group Travel management
 *
 * APIs for managing travels
 */
class TravelController extends Controller
{
    /**
     * Create travel
     *
     * This endpoint allows you to add new travel.
     *
     * @authenticated
     *
     * @apiResource App\Http\Resources\TravelResource
     **/
    public function store(StoreTravelRequest $request)
    {
        $travel = Travel::create($request->validated());

        return new TravelResource($travel);
    }

    /**
     * Update travel
     *
     * This endpoint allows you to update a travel.
     *
     * @authenticated
     *
     * @urlParam travel_id string required The ID of the travel. Example: hd3h-dh8h3-2b23ji
     *
     * @apiResource App\Http\Resources\TravelResource
     **/
    public function update(Travel $travel, UpdateTravelRequest $request)
    {
        $travel->update($request->validated());

        return new TravelResource($travel);
    }
}
