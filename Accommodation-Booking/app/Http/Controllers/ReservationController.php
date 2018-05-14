<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reservation;
use Validator;
use Illuminate\Support\Facades\DB;
use Debugbar;

class ReservationController extends Controller
{
    public function index()
    {
        return Reservation::all();
    }

    public function store(Request $request)
    {
        $from_date = $request->input('start_date');
        $to_date = $request->input('end_date');

        $validatedData = $request->validate([
          'property_id' => 'required',
          'start_date' => 'required|date|after:yesterday',
          'end_date' => 'required|date|after:start_date'
        ]);

        //Debugbar::info($validatedData);

        //We should check if user reservation is really Available
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
        " AND ifnull(b.checkin,0)<>-1".
        " AND p.id = ".$request->input('property_id');

        Debugbar::info('$searchQuery: '.$searchQuery);

        $searchResult = DB::select($searchQuery);

        if (count($searchResult)) {
          if($searchResult[0]->{'available_from'} <= $from_date AND $searchResult[0]->{'available_to'} >= $to_date) {
              $reservation = Reservation::create($validatedData);
              return response()->json($reservation, 201);
          }
          else {
              return response()->json(['msg'=>'Dates for this Property are not available'], 422);
          }
        }
        else {
            return response()->json(['msg'=>'Dates for this Property are not available'], 422);
        }
    }

    public function delete(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(null, 204);
    }
}
