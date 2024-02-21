<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
     * Retrieve public travels
     *
     * This endpoint allows you to retrieve paginated public travels record.
     *
     * @apiResourceCollection App\Http\Resources\TravelResource
     *
     * @apiResourceModel App\Models\Travel
     **/
    public function index()
    {
        $travels = Travel::where('is_public', true)->paginate();

        return TravelResource::collection($travels);
    }
}
