<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;

/**
 * @group Admin endpoints
 */
class TravelController extends Controller
{
    /**
     * pOST Travel
     * 
     * Create a new Travel record.
     * 
     * @authenticated 
     * 
     * @response {"data":{"id":"9c55789a-07ea-4a87-b3ac-424a48ebdf6b","name":"Main-travel-update","slug":"travel-2","description":"For testing parameter","number_of_days":"10","number_of_nights":9}} 
     * @response 422 {"errors":{"name":["The name has already been taken."]}}
     */
    public function store(TravelRequest $request)
    {
        $travel = Travel::create($request->validated());

        return new TravelResource($travel);
    }

    public function update(Travel $travel, TravelRequest $request)
    {
        $travel->update($request->validated());

        return new TravelResource($travel);
    }
}
