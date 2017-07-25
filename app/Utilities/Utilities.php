<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 7/25/17
 * Time: 4:42 PM
 */

namespace App\Utilities;


use Illuminate\Support\Facades\Log;

class Utilities
{
    // Converts the Array of Objects to an Array
    // If a key is provided, then the values become the keys for each entry
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
}