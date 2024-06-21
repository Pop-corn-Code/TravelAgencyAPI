<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TourController extends Controller
{
    public function index(Travel $travel, Request $request){
       
        try {
            $validated = $request->validate([
                'priceFrom' => 'nullable|numeric',
                'priceTo' => 'nullable|numeric',
                'dateFrom' => 'nullable|date',
                'dateTo' => 'nullable|date',
                'sortBy' => ['nullable', Rule::in(['price'])],
                'sortOrder' => ['nullable', Rule::in(['asc', 'desc'])],
            ], [
                'sortBy.in' => "The 'sortBy' parameter accepts only the 'price' value.",
                'sortOrder.in' => "The 'sortOrder' parameter accepts only 'asc' or 'desc' values.",
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }


        $tours = $travel->tours()
            ->when($request->priceFrom, function($query) use ($request){
                $query->where('price', '>=', $request->priceFrom * 100);
            })
            ->when($request->priceTo, function($query) use ($request){
                $query->where('price', '<=', $request->priceTo * 100);
            })
            ->when($request->dateFrom, function($query) use ($request){
                $query->where('starting_date', '>=', $request->dateFrom);
            })
            ->when($request->dateTo, function($query) use ($request){
                $query->where('starting_date', '<=', $request->dateTo);
            })
            ->when($request->sortBy && $request->sortOrder, function($query) use ($request){
                $query->orderBy($request->sortBy, $request->sortOrder);
            })
            ->orderBy('starting_date')
            ->paginate();

        return TourResource::collection($tours);
    }

}
