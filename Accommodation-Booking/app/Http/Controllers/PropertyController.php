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
                      ->leftjoin('availabilities', 'properties.id','=','availabilities.property_id')
                      ->where('availabilities.start_date', '<=', $to_date)
                      ->where('availabilities.end_date', '>=', $from_date)
                      ->where('properties.id', '=', 1)
                      ->select('properties.id as property_id', 'properties.name', 'properties.address', 'properties.description', 'availabilities.*')
                      ->get();*/

        $searchQuery = " SELECT p.id property_id, p.name as property_name,".
        " ifnull(b.checkin,'".$from_date."') as available_from,".
        " ifnull(b.checkout,'".$to_date."') as available_to".
        " FROM properties p".
        " inner join availabilities a on a.property_id=p.id".
        " LEFT OUTER JOIN ".
        " (".
        "    SELECT rooms.id, rooms.name ,".
        "		   max(case".
        "			  when '".$from_date."' >= bookings.start_date and '".$to_date."' <= bookings.end_date then -1".
        "			  when '".$from_date."' < bookings.start_date then '".$from_date."' ".
        "			  when '".$from_date."' < bookings.end_date then bookings.end_date ".
        "			  else '".$from_date."' end) as checkin,".
        "		   max(case  ".
        "			  when '".$from_date."' >= bookings.start_date and '".$to_date."' <= bookings.end_date then -1".
        "			  when '".$to_date."' > bookings.start_date and  '".$from_date."' < bookings.start_date then bookings.start_date ".
        "			  when '".$from_date."' < bookings.start_date and  '".$to_date."' > bookings.start_date and '".$to_date."' < bookings.end_date then bookings.end_date ".
        "			  when '".$to_date."' > (select max(end_date) from reservations where property_id= bookings.property_id) then  '".$to_date."'".
        "	      else null".
        "		   end) as checkout".
        "	  FROM properties rooms".
        "	  left outer join reservations bookings  on bookings.property_id = rooms.id".
        "	  inner join availabilities availabilities on availabilities.property_id = rooms.id".
        "	  WHERE  (bookings.start_date BETWEEN  '".$from_date."' and  '".$to_date."' OR bookings.end_date BETWEEN  '".$from_date."' and  '".$to_date."')".
        "	  group by rooms.id, rooms.name ".
        " ) b ON b.ID = p.id".
        " WHERE '".$from_date."' >= a.start_date  AND  '".$to_date."' <= a.end_date ".
        " AND ifnull(b.checkin,0)<>-1";

        Debugbar::info('$searchQuery: '.$searchQuery);

        $searchResult = DB::select($searchQuery);
        Debugbar::info($searchResult);

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
