<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminLocationController extends Controller
{
    public function countries(Request $request)
    {
        return Inertia::render('Admin/Locations/Index', [
            'kind' => 'countries',
            'items' => Country::query()->withCount('cities')->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('ar_name', 'like', "%{$search}%")))->orderBy('name')->paginate(20)->withQueryString(),
            'search' => $request->input('search', ''),
        ]);
    }

    public function cities(Request $request)
    {
        return Inertia::render('Admin/Locations/Index', [
            'kind' => 'cities',
            'items' => City::query()->with('country:id,name,ar_name')->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('ar_name', 'like', "%{$search}%")))->orderBy('name')->paginate(20)->withQueryString(),
            'countries' => Country::orderBy('name')->get(['id', 'name', 'ar_name']),
            'search' => $request->input('search', ''),
        ]);
    }

    public function storeCountry(Request $request)
    {
        $data = $request->validate($this->countryRules());
        Country::create($data);

        return back()->with('success', 'Country created.');
    }

    public function updateCountry(Request $request, Country $country)
    {
        $country->update($request->validate($this->countryRules()));

        return back()->with('success', 'Country updated.');
    }

    public function destroyCountry(Country $country)
    {
        abort_if(City::where('country_id', $country->id)->exists(), 422, 'Remove its cities first.');
        abort_if(DB::table('users')->where('nationality', $country->id)->orWhere('residence', $country->id)->exists(), 422, 'This country is used by members.');
        $country->delete();

        return back()->with('success', 'Country deleted.');
    }

    public function storeCity(Request $request)
    {
        City::create($request->validate($this->cityRules()));

        return back()->with('success', 'City created.');
    }

    public function updateCity(Request $request, City $city)
    {
        $city->update($request->validate($this->cityRules()));

        return back()->with('success', 'City updated.');
    }

    public function destroyCity(City $city)
    {
        abort_if(DB::table('users')->where('city', $city->id)->exists(), 422, 'This city is used by members.');
        $city->delete();

        return back()->with('success', 'City deleted.');
    }

    private function countryRules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'ar_name' => ['nullable', 'string', 'max:255'], 'flag' => ['required', 'string', 'max:10'], 'is_arab' => ['boolean']];
    }

    private function cityRules(): array
    {
        return ['country_id' => ['required', 'integer', Rule::exists('countries', 'id')], 'name' => ['required', 'string', 'max:255'], 'ar_name' => ['nullable', 'string', 'max:255']];
    }
}
