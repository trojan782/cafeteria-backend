<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class MealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Collection|Meal[]
     */
    public function index()
    {
        return Meal::all();
    }

    public function create(Request $request)
    {
        try {
            if (Gate::allows('create-meal')) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string',
                    'qty' => 'numeric',
                    'price' => 'numeric'
                ]);
                if($validator->fails()){
                    return response()->json(json_decode($validator->errors()->toJson()), 400);
                }
                return Meal::create(array_merge(
                    $validator->validated()
                ));

            } else {
                throw new \Exception("Forbidden resource");
            }
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'meal' => 'required|array|min:1',
                'meal.*.id' => 'required|uuid',
                'meal.*.name' => 'required|string',
                'meal.*.price' => 'required|numeric',
                'meal.*.qty' => 'numeric',
            ]);
            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }
            return $request->all();
        } catch(\Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Meal $meal
     * @return Response
     */
    public function show(Meal $meal): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Meal $meal
     * @return Response
     */
    public function edit(Meal $meal): Response
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Meal $meal
     * @return Response
     */
    public function destroy(Meal $meal): Response
    {
        //
    }
}
