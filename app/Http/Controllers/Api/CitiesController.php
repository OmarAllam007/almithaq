<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitiesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ]);

        $cities = City::where('country_id', $request->country_id)
            ->select(['id', 'name', 'ar_name'])
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }
}
