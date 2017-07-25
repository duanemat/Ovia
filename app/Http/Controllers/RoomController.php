<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{

    function getAvailability(Request $request, $room_id=null){

        $available_rooms = null;

        // Make sure the querystring includes the requested start date and length of stay.
        // If not provided, assume today and length of 1.
        $start_date = $request->input('start_date', date("m-d-Y"));
        $los = $request->input('los', 1);

        
        // If you only want availability for a specific room
        if (($room_id != null) && (is_numeric($room_id))){
            $available_rooms = DB::select('SELECT Room.id AS room_id FROM Room ');
        }else{

        }
    }
}