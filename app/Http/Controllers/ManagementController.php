<?php

namespace App\Http\Controllers;

use App\Utilities\Utilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagementController extends Controller
{
    /**
     * @param Request $request
     */
    function getMaintenanceSchedule(Request $request, $maintenance_team_id=1){

        // Retrieve the maintenance information for the team specified
        $maintenance_team_info = DB::select("SELECT * FROM Maintenance WHERE id = ?", [$maintenance_team_id]);
        if ($maintenance_team_info == null){
            return ["result"=>null, "error"=>"No maintenance schedule found for team $maintenance_team_id"];
        }

        $cleaning_date = \DateTime::createFromFormat('m-d-Y', $request->input('cleaning_date', date("m-d-Y")));
        $cleaning_date->modify('-1 day');

        // Today's cleaning schedule is based off yesterday's bookings, so going to retrieve yesterday's schedule.
        // I am assuming we clean every day regardless of whether or not guest is staying multiple days.  If not, then
        // change logic to only focus on the end of a guest's stay.
        $rooms_to_clean = Utilities::getReservationsForDay($cleaning_date->format('Y-m-d'));

        foreach($rooms_to_clean as $room){
            $room_id = $room['room_id'];
            if (!isset($rooms_needing_a_clean[$room_id])){
                $rooms_needing_a_clean[$room_id] = $maintenance_team_info[0]->time_clean_per_room;
            }

            $rooms_needing_a_clean[$room_id] += $room['guests_count'] * $maintenance_team_info[0]->time_clean_per_guest;


        }

        // Peak at requested day's schedule and see which rooms are going to be used.  Prioritize those.
        $prioritized_date = \DateTime::createFromFormat('m-d-Y', $request->input('cleaning_date', date("m-d-Y")));
        $prioritized_rooms_to_clean = Utilities::getReservationsForDay($prioritized_date->format('Y-m-d'));

        $room_cleaning_order = [];
        $clean_room_array = array_keys($rooms_needing_a_clean);

        foreach($prioritized_rooms_to_clean as $prioritized_room){
            // If found, then these rooms have quick turnover so clean them.
            // But if they are going to be used today but weren't yesterday, I'm assuming they're clean.
            if (in_array($prioritized_room['room_id'], $clean_room_array) && !in_array($prioritized_room['room_id'], $room_cleaning_order)){
                array_unshift($room_cleaning_order, $prioritized_room['room_id']);
            }
        }


        // Total number of rooms to clean.
        // Now cycle through and make sure we have enough time for cleaning crew within 8 hours.
        $room_cleaning_order = array_merge($room_cleaning_order, array_diff($clean_room_array, $room_cleaning_order));

        $total_cleaning_time = 0;
        $maximum_time_cleaning = $maintenance_team_info[0]->max_hours_day * 60;
        $cleaning_crew_schedule = [];
        foreach($room_cleaning_order as $room){
            if ($rooms_needing_a_clean[$room] + $total_cleaning_time <= $maximum_time_cleaning){
                array_push($cleaning_crew_schedule, (string) $room);
                $total_cleaning_time += $rooms_needing_a_clean[$room];
            }
        }
        return [$cleaning_crew_schedule, null];
    }
}
