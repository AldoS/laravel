<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Availability;
use App\Property;
use Validator;
use Illuminate\Support\Facades\DB;
use Debugbar;

class AvailabilityController extends Controller
{
    public function index()
    {
        return Availability::all();
    }

    public function show($id)
    {
        $availability = Availability::find($id);
        if(is_null($availability)) {
            return response()->json(null, 404);
        }
        return response()->json(Availability::findOrFail($id), 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
          'property_id' => 'required',
          'start_date' => 'required|date',
          'end_date' => 'required|date|after:start_date'
        ]);

        $vPropertyId = $request->input('property_id');

        $property = Property::find($vPropertyId);
        if(is_null($property)) {
            return response()->json(['msg' => 'Property with id '.$vPropertyId.' does not exist!'], 404);
        }
        else {
            $availability = Availability::create($validatedData);
        }

        //$availability = Availability::create($validatedData);
        return response()->json($availability, 201);
    }

    public function update(Request $request, Availability $availability)
    {
        $availability->update($request->all());

        return response()->json($availability, 200);
    }

    public function delete(Availability $availability)
    {
        $availability->delete();

        return response()->json(null, 204);
    }
}
