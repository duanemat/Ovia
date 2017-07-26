<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Rooms Controller
// Availability
$app->get('availability', 'RoomController@getAvailability');
$app->get('availability/{room_id}', 'RoomController@getAvailability');

// Booking room
$app->post('rooms', 'RoomController@bookRoom');
$app->post('rooms/{room_id}', 'RoomController@bookRoom');

// Cleaning Schedule
$app->get('maintenance', 'ManagementController@getMaintenanceSchedule');
$app->get('maintenance/{maintenance_team_id}', 'ManagementController@getMaintenanceSchedule');
