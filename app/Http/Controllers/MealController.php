<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'qty' => 'required|numeric',
                'price' => 'required|numeric'
            ]);
            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        //
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
