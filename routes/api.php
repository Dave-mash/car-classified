<?php

use Illuminate\Http\Request;

use App\User;
use App\Car;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Get all users
Route::get('/', 'HomeController@index')->middleware('cors');

// fetch all cars
Route::get('cars', 'CarController@index')->middleware('cors');
// fetch single car
Route::get('car/{id}', 'CarController@show');

/*
User routes
*/

// Get all users
Route::get('users', 'UserController@index')->middleware('cors');
// Post a user
Route::post('register', 'UserController@store')->middleware('cors');
// Log in a user
Route::post('login', 'UserController@login')->middleware('cors');
// Admin log in
Route::post('admin_login', 'UserController@admin_login')->middleware('cors');

 
Route::group([
    'middleware' => 'auth.jwt',
    'middleware' => 'cors'
], function () {
    Route::get('logout/{id}', 'UserController@logout');

    // Update a user
    Route::put('user/{id}', 'UserController@update');
    // Delete a user
    Route::delete('user/{id}', 'UserController@destroy');
    // Fetch authorized user
    Route::get('user', 'UserController@getAuthUser');

    /*
    Cart routes
    */

    // fetch cart
    Route::get('cart/{userId}', 'CartController@index');
    // add to cart
    Route::post('cart/{userId}/{carId}', 'CartController@store');
    // Show single cart
    Route::get('cart/{userId}/{cartId}', 'CartController@show');
    // delete cart
    Route::delete('cart/{userId}/{cartId}', 'CartController@destroy');
    // clear cart
    Route::delete('cart/{userId}', 'CartController@clearCart');
    // Checkout
    Route::patch('checkout/{userId}', 'CartController@checkout');


    /*
    Car routes
    */

    // fetch vendor cars
    Route::get('{vendorId}/cars', 'CarController@vendorCars');
    // create new car
    Route::post('car/{id}', 'CarController@store');
    // update car
    Route::put('car/{vendorId}/{carId}', 'CarController@update');
    // delete a car
    Route::delete('car/{vendorId}/{carId}', 'CarController@destroy');
    // fetch purchased car
    Route::get('purchased_cars/{vendorId}', 'CarController@purchased');
});