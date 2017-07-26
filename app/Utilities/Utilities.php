<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 7/25/17
 * Time: 4:42 PM
 */

namespace App\Utilities;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Utilities
{

    /**
     * Converts the Array of Objects to an Array
     * If a key is provided, then the values become the keys for each entry
     * @param $object Object to convert to Array
     * @param null $key Key to use to create associative array, if present.
     * @return array Object as an Array
     */
    public static function convertObjectToArray($object, $key=null){

        if ($key != null){
            $temp = [];
            foreach($object as $obj){
                $temp[$obj->{$key}] = $obj;
            }
            $object = $temp;
        }
        return collect($object)->map(function($x){ return (array) $x;})->toArray();
    }

    /**
     * Retrieve an Array of the Reservations for the provided date
     * @param $date Date to retrieve Reservations for.  Format should be Y-m-d
     * @return array Array of Reservations for the day
     */
    public static function getReservationsForDay($date){
        $reservations = DB::select("SELECT room_id, guests_count, storage_count
                                    FROM Reservation
                                    WHERE stay_date_start = ?", [$date]);
        $reservations = self::convertObjectToArray($reservations);

        return $reservations;
    }
}