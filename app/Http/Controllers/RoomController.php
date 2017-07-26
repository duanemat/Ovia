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
            // Return all rooms, since we can try to break people up into different rooms if it makes sense.
            $possible_rooms = DB::select("SELECT Room.id AS room_id, Room.guests, Room.storage 
                                      FROM Room");


            // Get all the room IDs
            foreach ($possible_rooms as $room) {
                $room_ids[] = $room->room_id;
            }
        }else{
            // Make sure room specified meets criteria for number of guests and amount of luggage
            $possible_rooms = DB::select("SELECT Room.id AS room_id, Room.guests, Room.storage
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
                                    WHERE (res.stay_date_start >= ? AND res.stay_date_start < ?) AND
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


            // This room is booked with the maximum number of people, so don't include it in the remaining set
            if ($remaining_guests_space == 0) {
                unset($possible_rooms[$room_id]);
                continue;
            }else{
                $possible_rooms[$room_id]["guests"] = $remaining_guests_space;
                $possible_rooms[$room_id]["storage"] = $remaining_storage_space;
                // Mark this as a partial match; we'd prefer to put this user here to optimize space.
                $possible_rooms[$room_id]['partial'] = true;
            }
        }

        $available_rooms = $possible_rooms;
        return $available_rooms;
    }

    private function insertBooking($parameters){

        $row_id = DB::table("Reservation")->insert([
            'room_id'=>$parameters['room_id'],
            'stay_date_start'=>$parameters['stay_date_start'],
            'stay_date_end'=>$parameters['stay_date_end'],
            'guests_count'=>$parameters['guests_count'],
            'storage_count'=>$parameters['storage_count'],
            'check_in_time'=>$parameters['check_in_time'],
            'guest_id'=>$parameters['guest_id'],
            'created_at'=>time(),
            'updated_at'=>time()
        ]);

        return $row_id;
    }

    private function parseRequest($request){

        /* Possible fields in the request:
         * start_date - first date of stay, formatted m-d-Y (default: today)
         * los - length of stay in days (default: 1)
         * num_guests - number of guests (default: 1)
         * num_luggage - number of pieces of luggage (default: 1)
         * check_in - time to check in between 12 (12 pm) and 20 (8pm) (default: 12)
         * guest_id - Guest id for this request (default: "")
         */

        $start_date = \DateTime::createFromFormat('m-d-Y', $request->input('start_date', date("m-d-Y")));
        $los = $request->input('los', 1);
        $num_guests = $request->input('num_guests', 1);
        $num_luggage = $request->input('num_luggage', 1);
        $check_in = $request->input('check_in', 12);
        $guest_id = $request->input('guest_id', "");

        // In case we have an illogical length of stay
        if ($los <= 0)
            $los = 1;

        // Compute the end date
        $end_date = (clone $start_date)->add(new \DateInterval("P".$los."D"));

        return [$start_date, $end_date, $los, $num_guests, $num_luggage, $check_in, $guest_id];
    }

    /**
     * @param Request $request Request object
     * @param integer $room_id Optional room id to check availability for
     * @return array - An array of available rooms for specified dates, which can be empty if none found.
     */
    function getAvailability(Request $request, $room_id=null){

        $available_rooms = null;
        $date_format = "Y-m-d";

        list($start_date, $end_date, $los, $num_guests, $num_luggage, $check_in, $guest_id) = $this->parseRequest($request);

        $available_rooms = $this->retrieveAvailabilityWithConditions($room_id, $start_date->format($date_format), $end_date->format($date_format), $num_guests, $num_luggage, $check_in);

        return $available_rooms;
    }


    function bookRoom(Request $request, $room_id = null){

        $date_format = "Y-m-d";
        list($start_date, $end_date, $los, $num_guests, $num_luggage, $check_in, $guest_id) = $this->parseRequest($request);
        $available_rooms = $this->getAvailability($request, $room_id);


        /* Cycle through the available rooms and then try to find the most "efficient" booking.
         * Efficiency in this case follows this pattern:
         * - If I can fill a partially-filled room with someone, then I'll put them in first.  That cuts down on cleaning time and
         * leaves more rooms open.
         * - If no partial rooms are available, then I focus on the "best" match in terms of closest to
         */

        // I am making the assumption here that when you are trying to book, you are sending a discrete "unit" of guests and luggage.
        // And you don't want to break that up; in other words, if 2 people want to stay somewhere with 2 luggage, I'm not going
        // to break them up.  Instead, if they were cool being broken up, they'd just make two individual booking requests.

        $booked_room = null;
        foreach($available_rooms as $available_room){

            $remaining_guests_space = $available_room['guests'] - $num_guests;
            $remaining_storage_space = $available_room['storage'] - $num_luggage;

            // This room won't fit the requested booking.
            if (($remaining_storage_space < 0) || ($remaining_guests_space < 0)){
                continue;
            }

            // Accept the first match
            if ($booked_room == null){
                $booked_room = $available_room;
                $booked_room['guests'] = $remaining_guests_space;
                $booked_room['storage'] = $remaining_storage_space;
                continue;
            }

            // We have a partially-filled room, so let's see if we can fit them in.
            // With more business logic, we'd look into multiple partially-filled places being better to be less open spaces
            // Or less storage.
            if(isset($available_room['partial'])){
                $booked_room = $available_room;
                $booked_room['guests'] = $remaining_guests_space;
                $booked_room['storage'] = $remaining_storage_space;
            }else{
                // If we already have a match for "partial", then skip because we want to fill partial.
                if (isset($booked_room['partial']))
                    continue;

                // If this room would give more fully-packed rooms, then choose that one.
                if ($booked_room['guests'] >= $remaining_guests_space){
                    $booked_room = $available_room;
                }
            }

        }


        // If booked_room is not null, save to database and return the ID
        if ($booked_room == null){
            return ["result"=>null, "error"=>"No rooms available for ".$start_date->format($date_format)];
        }else{
            try {
                $parameters = [
                    'room_id' => $booked_room['room_id'],
                    'stay_date_start' => $start_date->format($date_format),
                    'stay_date_end' => $end_date->format($date_format),
                    'guests_count' => $num_guests,
                    'storage_count' => $num_luggage,
                    'check_in_time' => $check_in,
                    'guest_id' => $guest_id,
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $row_id = $this->insertBooking($parameters);

            }catch (\Exception $e){
                Log::error($e->getMessage());
                $row_id = null;
            }
            if ($row_id == null){
                return ["result"=>null, "error"=>"Error recording this booking."];
            }else{
                return ["result"=>$booked_room['room_id'], "error"=>null];
            }
        }
    }
}