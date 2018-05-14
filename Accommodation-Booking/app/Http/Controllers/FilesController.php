<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Property;
use Debugbar;

class FilesController extends Controller
{
    public function create(Request $request)
    {
        Debugbar::info($request);
        Debugbar::info($request->file('image'));
        Debugbar::info($request->input('property_id'));

        $validatedData = $request->validate([
            'property_id' => 'required|max:200',
            'image' => 'required|image'
        ]);

        $vNewPropertyId = $request->input('property_id');

        $property = Property::find($vNewPropertyId);
        if(is_null($property)) {
            return response()->json(null, 404);
        }
        else {
          //$path = $request->file('image')->store('/PropertImages');
          $path = $request->file('image')->storeAs('/PropertyImages', 'property-'.$vNewPropertyId.'.jpeg');
          $property->imageUrl = $path;
          $property->save();
          return response()->json(['path' => $path], 200);
        }
    }
}
