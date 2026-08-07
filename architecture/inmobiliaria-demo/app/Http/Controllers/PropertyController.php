<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Property;
use App\Models\Sector;
use App\Models\PropertyType;
use App\Models\Operation;

class PropertyController extends Controller
{
    public function home()
    {
        $featured = Property::with(['sector.city', 'propertyType', 'operation', 'images'])
            ->where('status', 'available')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(6)
            ->get();

        $latest = Property::with(['sector.city', 'propertyType', 'operation', 'images'])
            ->where('status', 'available')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('home', compact('featured', 'latest'));
    }

    public function index()
    {
        $query = Property::with(['sector.city', 'propertyType', 'operation', 'images'])
            ->where('status', 'available');

        if ($sector = request('sector')) {
            $query->where('sector_id', $sector);
        }
        if ($city = request('city')) {
            $query->whereHas('sector', fn ($q) => $q->where('city_id', $city));
        }
        if ($type = request('type')) {
            $query->where('property_type_id', $type);
        }
        if ($op = request('operation')) {
            $query->where('operation_id', $op);
        }
        if ($min = request('price_min')) {
            $query->where('price', '>=', $min);
        }
        if ($max = request('price_max')) {
            $query->where('price', '<=', $max);
        }
        if ($beds = request('bedrooms')) {
            $query->where('bedrooms', '>=', $beds);
        }
        if ($baths = request('bathrooms')) {
            $query->where('bathrooms', '>=', $baths);
        }
        if ($code = request('code')) {
            $query->where('code', 'LIKE', "%{$code}%");
        }
        if ($areaMin = request('area_min')) {
            $query->where('area_m2', '>=', $areaMin);
        }
        if ($areaMax = request('area_max')) {
            $query->where('area_m2', '<=', $areaMax);
        }
        if ($parking = request('parking')) {
            $query->where('parking_spaces', '>=', $parking);
        }

        $properties = $query->orderBy('published_at', 'desc')->paginate(12)->withQueryString();

        $cities     = City::whereHas('sectors', fn ($q) => $q->where('visibility', 'visible'))->get();
        $sectors    = Sector::where('visibility', 'visible')->get();
        $types      = PropertyType::where('visibility', 'visible')->get();
        $operations = Operation::where('visibility', 'visible')->get();

        return view('properties.index', compact('properties', 'cities', 'sectors', 'types', 'operations'));
    }

    public function show(Property $property)
    {
        abort_if($property->status !== 'available', 404);

        $property->load(['sector.city', 'propertyType', 'operation', 'images']);

        $related = Property::with(['sector', 'propertyType', 'operation', 'images'])
            ->where('status', 'available')
            ->where('sector_id', $property->sector_id)
            ->where('id', '!=', $property->id)
            ->whereHas('operation', fn ($q) => $q->where('visibility', 'visible'))
            ->whereHas('propertyType', fn ($q) => $q->where('visibility', 'visible'))
            ->take(3)
            ->get();

        return view('properties.show', compact('property', 'related'));
    }

    public function sectoresPorCiudad(City $city)
    {
        $sectores = Sector::where('city_id', $city->id)
            ->where('visibility', 'visible')
            ->get(['id', 'name']);

        return response()->json($sectores);
    }
}
