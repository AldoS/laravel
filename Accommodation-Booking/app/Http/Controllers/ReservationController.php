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
          'start_date' => 'required|date',//|after:yesterday',
          'end_date' => 'required|date|after:start_date'
        ]);

        //Debugbar::info($validatedData);

        //We should check if user reservation is really Available
        $searchQuery = "SELECT rooms.id, rooms.name ,".
        "  max(case when (select count(1) from reservations where property_id = rooms.id) = 0 then '".$from_date."'".
        "     when '".$from_date."' < bookings.start_date then '".$from_date."'".
        "     when '".$from_date."' < bookings.end_date then bookings.end_date else '".$from_date."' end) as checkin,".
        "  max(case  when '".$to_date."' > bookings.start_date and  '".$from_date."' < bookings.start_date then bookings.start_date".
        "     when '".$from_date."' < bookings.start_date and  '".$to_date."' > bookings.start_date and '".$to_date."' < bookings.end_date then bookings.end_date".
        "     when '".$to_date."' > (select max(end_date) from reservations where property_id= bookings.property_id) then  '".$to_date."'".
        "     when (select count(1) from reservations where property_id = rooms.id) = 0 then '".$to_date."'".
        "     else null".
        "    end) as checkout".
        " FROM properties rooms".
        " inner join availability availability on availability.property_id = rooms.id".
        " left outer join reservations bookings  on bookings.property_id = rooms.id".
        " WHERE  (('".$from_date."' < bookings.start_date or '".$to_date."'<bookings.start_date)".
        " OR ('".$from_date."'>bookings.end_date or '".$to_date."'>bookings.end_date)".
        " OR 0 = (select count(1) from reservations where property_id = rooms.id))".
        " AND availability.start_date <= '".$from_date."' AND availability.end_date >= '".$to_date."'".
        " group by rooms.id, rooms.name";

        Debugbar::info('$searchQuery: '.$searchQuery);

        $searchResult = DB::select($searchQuery);
        Debugbar::info($searchResult);
        Debugbar::info($searchResult[0]->{'id'});
        Debugbar::info($searchResult[0]->{'checkin'});
        Debugbar::info($searchResult[0]->{'checkout'});

        if($searchResult[0]->{'checkin'} <= $from_date AND $searchResult[0]->{'checkout'} >= $to_date) {
            $reservation = Reservation::create($validatedData);
            return response()->json($reservation, 201);
        }
        else {
            Debugbar::info('stop !!!');
            return response()->json(['msg'=>'Dates for this Property are not available'], 501);
        }
    }

    public function delete(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(null, 204);
    }
}
