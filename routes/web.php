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
$app->get('/availability', 'RoomController@getAvailability');
$app->get('/availability/{room_id}', 'RoomController@getAvailability');

$app->post('/rooms', 'RoomController@bookRoom');

// Cleaning Schedule
$app->get('/maintenance', 'MaintenanceController@getMaintenanceSchedule');

