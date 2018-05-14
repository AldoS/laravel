<?php

use Illuminate\Http\Request;
use App\Property;
use App\Reservation;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('properties', 'PropertyController@index');
Route::get('properties/{id}', 'PropertyController@show');
Route::post('properties', 'PropertyController@store');
//Route::put('properties/{property}', 'PropertyController@update');
Route::delete('properties/{property}', 'PropertyController@delete');
Route::post('properties/search', 'PropertyController@search');

Route::any('errors', 'PropertyController@errors');
Route::post('files/create', 'FilesController@create');

Route::get('availabilities', 'AvailabilityController@index');
Route::get('availabilities/{id}', 'AvailabilityController@show');
Route::post('availabilities', 'AvailabilityController@store');
Route::put('availabilities/{availability}', 'AvailabilityController@update');
Route::delete('availabilities/{availability}', 'AvailabilityController@delete');

Route::get('reservations', 'ReservationController@index');
Route::post('reservations', 'ReservationController@store');
Route::delete('reservations/{reservation}', 'ReservationController@delete');
