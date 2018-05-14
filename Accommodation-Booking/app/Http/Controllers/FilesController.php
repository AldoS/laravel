<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        //$path = $request->file('image')->store('/PropertImages');
        $path = $request->file('image')->storeAs('/PropertyImages', 'property-'.$vNewPropertyId.'.jpeg');
        return response()->json(['path' => $path], 200);
    }
}
