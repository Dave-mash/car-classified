<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;

use App\Http\Requests;
use App\Car;
use App\User;
use App\Http\Resources\Car as CarResource;
use Dotenv\Regex\Success;

function error() {
    return response()->json([
        'error' => 'You are not authorized to access this resource',
        'status' => 401
    ], 401);
}

class CarController extends Controller
{
    /**
     * Get all cars
     */
    public function index()
    {
        $car = Car::where('purchased', '=', false)->get();
        return CarResource::collection($car);            
    }

    /**
     * Post a car
     */
    public function store(Request $request, $id)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            $userObj = null;
            $user = null;

            if (!$userObj = JWTAuth::parseToken()->authenticate()) {
                return error();
            } elseif (!$user = User::where('id', '=', $userObj->id)->first()) {
                return error();
            } elseif ($userObj->id !== (int)$id) {
                return error();
            }

            // $picture = $request->input('picture');
            $car = new Car;
            $car->vendor_id = $id;
            $car->make = $request->input('make');
            $car->model = $request->input('model');
            $car->color = $request->input('color');
            $car->description = $request->input('description');
            $car->condition = $request->input('condition');
            $car->price = $request->input('price');
            $car->save();

            $user = User::find($id);
            $user->isAdmin = true;
            $user->save();
            $newCar = new CarResource($car);
            return [
                'message' => 'Car posted successfully',
                'status' => 201,
                'car' => $user
            ];
        } catch (JWTException $exception) {
            return response()->json([
                'error' => 'Something wrong happened',
                'status' => 400
            ]);
        }
    }

    /**
     * Display a car
     */
    public function show($id)
    {
        if (!Car::find($id)) {
            return [
                'error' => 'Car was not found or does not exist',
                'status' => 404
            ];
        }
        $car = Car::findOrFail($id);
        return new CarResource($car);
    }
    
    /**
     * Update a car
     */
    public function update(Request $request, $vendorId, $carId)
    {
        try {
            $this->validate($request, [
                'token' => 'required'
            ]);
    
            $userObj = null;
            $user = null;
    
            
            
            if (!$userObj = JWTAuth::parseToken()->authenticate()) {
                return error();
            } elseif (!$user = User::where('id', '=', $userObj->id)->first()) {
                return error();
            } elseif ($userObj->id !== (int)$vendorId) {
                return error();
            }

            if (!Car::find($carId)) {
                return [
                    'error' => 'Car does not exist',
                    'status' => 404
                ];
            }
    
            $carObj = Car::findOrFail($carId);

            if ($user->id == $carObj->vendor_id) {
    
                $carObj->make = $request->input('make');
                $carObj->model = $request->input('model');
                $carObj->color = $request->input('color');
                $carObj->description = $request->input('description');
                $carObj->price = $request->input('price');
                $carObj->condition = $request->input('condition');
                $carObj->picture = $request->input('picture');
                $carObj->save();
    
                $updatedCar = new CarResource($carObj);

                return [
                    'message' => 'Car updated successfully',
                    'status' => 201,
                    'car' => $updatedCar
                ];
            }

        } catch (JWTException $exception) {
            return response()->json([
                'error' => 'Something wrong happened',
                'status' => 400
            ]);
        }
        

    }
    
    /**
     * View vendor cars.
     *
     */
    public function vendorCars(Request $request, $vendorId)
    {

        try {
            $this->validate($request, [
                'token' => 'required'
            ]);
    
            $userObj = null;
            $user = null;
    
            
            
            if (!$userObj = JWTAuth::parseToken()->authenticate()) {
                return error();
            } elseif (!$user = User::where('id', '=', $userObj->id)->first()) {
                return error();
            } elseif ($userObj->id !== (int)$vendorId) {
                return error();
            } elseif ($user->isAdmin == false) {
                return error();
            }

            $cars = Car::where('vendor_id', '=', $vendorId)->get();
            return CarResource::collection($cars);

        } catch (JWTException $exception) {
            return response()->json([
                'status' => 400
            ], 400);
        }

    }

    /**
     * View purchased cars
     */
    public function purchased(Request $request, $vendorId)
    {

        try {
            $this->validate($request, [
                'token' => 'required'
            ]);

            $purchased = null;
            $user = null;
            
            if (!$userObj = JWTAuth::parseToken()->authenticate()) {
                return error();
            } elseif (!$user = User::where('id', '=', $userObj->id)->first()) {
                return error();
            } elseif ($userObj->id !== (int)$vendorId) {
                return error();
            } elseif ($user->isAdmin == false) {
                return error();
            }

            if ($cars = Car::where('vendor_id', '=', $vendorId)->get()) {
                if ($cars[0]->purchased == true) {
                    return CarResource::collection($cars);
                } else {
                    return response()->json([
                        'message' => 'No purchased cars found',
                        'error' => 404
                    ], 404);
                }               
            }

        } catch (JWTException $exception) {
            return response()->json([
                'status' => 400,
                'error' => $exception
            ], 400);
        };

        if (!$purchased = Car::find($vendorId)) {
            return [
                'error' => 'Car was not found or does not exist',
                'status' => 404
            ];
        }
    }
    

    /**
     * Remove a car
     */

    public function destroy(Request $request, $vendorId, $carId)
    {
        try {
            $this->validate($request, [
                'token' => 'required'
            ]);
    
            $userObj = null;
            $user = null;
            $carObj = null;
    
            

            if (!$carObj = Car::find($carId)) {
                return [
                    'error' => 'Car does not exist',
                    'status' => 404
                ];
            }

            
            if (!$userObj = JWTAuth::parseToken()->authenticate()) {
                return error();
            } elseif (!$user = User::where('id', '=', $userObj->id)->first()) {
                return error();
            } elseif ($userObj->id !== (int)$vendorId) {
                return error();
            } elseif ($user->id !== $carObj->vendor_id) {
                return error();
            }

            $carObj->delete();
    
            return response()->json([
                'message' => 'Deleted successfully',
                'status' => 200,
                'car' => new CarResource($carObj)
            ], 200);

        } catch(JWTException $exception) {
            return response()->json([
                'status' => 400
            ], 400);
        }
    }
}
