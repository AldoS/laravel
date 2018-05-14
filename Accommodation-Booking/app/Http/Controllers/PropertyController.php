<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Property;
use Validator;
use Illuminate\Support\Facades\DB;
use Debugbar;

class PropertyController extends Controller
{

    public function search(Request $request)
    {
        $validatedData = $request->validate([
          'from_date' => 'required|date|after:yesterday',
          'to_date' => 'required|date|after:from_date',
        ]);

        //$data = $request->get('data');  //get all Request data
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        /*$searchResult = DB::table('properties')
                      ->leftjoin('availability', 'properties.id','=','availability.property_id')
                      ->where('availability.start_date', '<=', $to_date)
                      ->where('availability.end_date', '>=', $from_date)
                      ->where('properties.id', '=', 1)
                      ->select('properties.id as property_id', 'properties.name', 'properties.address', 'properties.description', 'availability.*')
                      ->get();*/

        $searchQuery = "SELECT rooms.id, rooms.name ,".
        "  max(case when (select count(1) from reservations where property_id = rooms.id) = 0 then '".$from_date."'".
        "     when '".$from_date."' < bookings.start_date then '".$from_date."'".
        "     when '".$from_date."' < bookings.end_date then bookings.end_date".
        "     else '".$from_date."' end) as checkin,".
        "  max(case  when '".$to_date."' > bookings.start_date and  '".$from_date."' < bookings.start_date then bookings.start_date".
        "     when '".$from_date."' < bookings.start_date and  '".$to_date."' > bookings.start_date and '".$to_date."' < bookings.end_date then bookings.end_date".
        "     when '".$to_date."' > (select max(end_date) from reservations where property_id= bookings.property_id) then  '".$to_date."'".
        "     when '".$to_date."' < (select min(start_date) from reservations where property_id= bookings.property_id) then  '".$to_date."'".
        "     when (select count(1) from reservations where property_id = rooms.id) = 0 then '".$to_date."'".
        "     else null".
        "    end) as checkout".
        " FROM properties rooms".
        " left outer join reservations bookings  on bookings.property_id = rooms.id".
        " inner join availability availability on availability.property_id = rooms.id".
        " WHERE  ('".$from_date."' < bookings.start_date or '".$to_date."' < bookings.start_date)".
        " OR ('".$from_date."' > bookings.end_date or '".$to_date."' > bookings.end_date)".
        " OR 0 = (select count(1) from reservations where property_id = rooms.id)".
        " AND availability.start_date <= '".$from_date."' AND availability.end_date >= '".$to_date."'".
        " group by rooms.id, rooms.name";

        Debugbar::info('$searchQuery: '.$searchQuery);

        $searchResult = DB::select($searchQuery);
        Debugbar::info($searchResult);

        /*
        $properties = Property::all();
        $offers = array();
        foreach ($properties as $propertie) {
          $offers[$propertie['id']] = 1;//Property::where('id', $propertie['id']);
          //$offers[] = Property::where('article_id', $article['id'])->groupBy('user_id')->get();
        }*/


        return response()->json([
            'data' => $searchResult
        ], 200);
    }

    public function index()
    {
        return Property::all();
    }

    public function show($id)
    {
        $property = Property::find($id);
        if(is_null($property)) {
            return response()->json(null, 404);
        }
        return response()->json(Property::findOrFail($id), 200);
    }

    public function store(Request $request)
    {
        Debugbar::info($request);
        $validatedData = $request->validate([
            'name' => 'required|max:200',
            'address' => 'required|max:200',
            'description' => 'required|max:1000',
        ]);

        $property = Property::create($validatedData + ['imageUrl' => '']);
        //$property = Property::create($request->all() + ['imageUrl' => 'public/PropertImages/no-image.jpg']);
        /*Debugbar::info($property);
        Debugbar::info($property->{'id'});
        $vNewPropertyId = $property->{'id'};*/

        //Set Image to store
        //$path = $request->file('image')->storeAs('/PropertImages', 'propert-'.$vNewPropertyId.'.jpeg');

        //Update Property with image
        //This should be done after because we dont know the property id yet !!!
        /*$propertyUpd = Property::find($vNewPropertyId);
        $propertyUpd->timestamps = false;
        $propertyUpd->imageUrl = $path;
        $propertyUpd->save();*/

        return response()->json($property, 201);
    }

    public function update(Request $request, Property $property)
    {
        $property->update($request->all());
        return response()->json($property, 200);
    }

    public function delete(Property $property)
    {
        $property->delete();
        return response()->json(null, 204);
    }

    public function errors()
    {
        return response()->json(['msg'=>'Not Implemented'], 501);
    }
}
