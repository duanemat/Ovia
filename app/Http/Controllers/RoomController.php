<?php

namespace App\Http\Controllers;

use App\Utilities\Utilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{

    /*
     * @param $start_date
     * @param $end_date
     * @param $num_guests
     * @param $num_luggage
     * @param $check_in
     * @return mixed
     */
    private function retrieveAvailabilityWithConditions($room_id,
                                                     $start_date,
                                                     $end_date,
                                                     $num_guests,
                                                     $num_luggage,
                                                     $check_in)
    {
        $available_rooms = [];
        $possible_rooms = [];
        $room_ids = [];
        // While I would probably weight the differences in performance and maintability between breaking this into
        // 3 different types queries or combined queries in production, for this exercise I'm going for simplicity.

        if($room_id == null) {
            // Return the Rooms that meet the number of guests and number of luggage
            $possible_rooms = DB::select("SELECT Room.id as room_id, Room.guests, Room.storage 
                                      FROM Room 
                                      WHERE (guests >=?) AND 
                                      (storage >= ?)", [$num_guests, $num_luggage]);


            // Check if rooms already rented and factor accordingly.
            foreach ($possible_rooms as $room) {
                $room_ids[] = $room->room_id;
            }
        }else{
            // Make sure room specified meets criteria for number of guests and amount of luggage
            $possible_rooms = DB::select("SELECT Room.id as room_id, Room.guests, Room.storage
                                     FROM Room
                                     WHERE Room.id == ?", [$room_id]);

            // If the requested room doesn't accomodate the number of guests/luggage, then we just return empty set.
            if (($possible_rooms[0]->guests < $num_guests) || ($possible_rooms[0]->storage < $num_luggage)){
                return [];
            }

            $room_ids = [$room_id];
        }

        $possible_rooms =  Utilities::convertObjectToArray($possible_rooms, 'room_id');

        // See booked rooms for that timeframe that match the valid Room IDs for this particular guest
        // For some reason SQLite doesn't play nice with value insertion for IN statements.
        $rooms_str = sprintf("(%s)", implode(",", $room_ids));

        $booked_rooms = DB::select("SELECT res.room_id, res.guests_count, res.storage_count
                                    FROM Reservation res
                                    WHERE res.stay_date_start 
                                    BETWEEN ? AND ? AND
                                    res.room_id IN ".$rooms_str, [$start_date, $end_date]);

        $booked_rooms = Utilities::convertObjectToArray($booked_rooms);

        // Now cycle through the booked rooms and see if, for the matches, we still could make it work with the given
        // guest and luggage count.  If not, then remove them from the possible rooms list

        foreach($booked_rooms as $booked_room){
            $room_id = $booked_room['room_id'];

            if(!isset($possible_rooms[$room_id]))
                continue;
            
            $remaining_guests_space = $possible_rooms[$room_id]['guests'] - $booked_room['guests_count'];
            $remaining_storage_space = $possible_rooms[$room_id]['storage'] - $booked_room['storage_count'];

            // We exceed the allowable number of space or storage, so remove from possible rooms
            if (($num_guests > $remaining_guests_space) || ($num_luggage > $remaining_storage_space)) {
                unset($possible_rooms[$room_id]);
                continue;
            }
        }

        $available_rooms = $possible_rooms;
        return $available_rooms;
    }

    function getAvailability(Request $request, $room_id=null){

        $available_rooms = null;
        $date_format = "Y-m-d";

        /* Possible fields in the request:
         * start_date - first date of stay, formatted m-d-Y (default: today)
         * los - length of stay in days (default: 1)
         * num_guests - number of guests (default: 1)
         * num_luggage - number of pieces of luggage (default: 1)
         * check_in - time to check in between 12 (12 pm) and 20 (8pm) (default: 12)
         */

        $start_date = \DateTime::createFromFormat('m-d-Y', $request->input('start_date', date("m-d-Y")));
        $los = $request->input('los', 1);
        $num_guests = $request->input('num_guests', 1);
        $num_luggage = $request->input('num_luggage', 1);
        $check_in = $request->input('check_in', 12);

        // In case we have an illogical length of stay
        if ($los <= 0)
            $los = 1;

        // Compute the end date
        $end_date = (clone $start_date)->add(new \DateInterval("P".$los."D"));

        $available_rooms = $this->retrieveAvailabilityWithConditions($room_id, $start_date->format($date_format), $end_date->format($date_format), $num_guests, $num_luggage, $check_in);

        return $available_rooms;
    }
}