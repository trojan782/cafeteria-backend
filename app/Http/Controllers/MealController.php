<?php

namespace App\Http\Controllers;

use App\Models\FoodHistory;
use App\Models\Meal;
use App\Models\User;
use App\Models\Wallet;
use CodeItNow\BarcodeBundle\Utils\QrCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public function store(Request $request, User $user, Wallet $wallet, QrCode $qrCode): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'string',
                'meal' => 'required|array|min:1',
                'meal.*.id' => 'required|uuid',
                'meal.*.name' => 'required|string',
                'meal.*.price' => 'required|numeric',
                'meal.*.qty' => 'numeric',
            ]);
            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }
            //Loop through the ticket
            $meals = $request->all()['meal'];
            $price = array();
            foreach ($meals as $m){
                array_push($price, $m['price']);
            }
            $info = $user->find(auth()->user()->id)->wallet;
            //Get total price of food purchased
            $total = array_sum($price);
            if($info->balance < $total){
                throw new \Exception ("You can't purchase food due to insufficient balance");
            }
            //deduct from wallet , food qty
            $update = $wallet->find($info->id)->update([
                'balance' => $info->balance - $total
            ]);
            //Save data to transactions table
            $history = FoodHistory::create([
                'user_id' => auth()->user()->id,
                'amount' => $total,
                'items' => $meals,
                'description' => $request->input('description')
            ]);
            $qr = $qrCode
                ->setText($history->id)
                ->setSize(300)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
                ->setLabel("LMU TICKET")
                ->setLabelFontSize(16)
                ->setImageType(QrCode::IMAGE_TYPE_PNG);
            $contentType = $qr->getContentType();
            $generate = $qr->generate();
            $barcode = "data:$contentType;base64,$generate";
            if($update == 1 && $history->id){
                return $this->dataResponseWithCode("You successfully purchased a meal of NGN $total", $barcode,$meals);
            }
            //print ticket
        } catch(\Exception $e) {
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    public function show(Meal $meal)
    {
        try {
            return User::find(auth()->user()->id)->history;
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'qty' => 'numeric',
                'price' => 'numeric',
                'availability' => 'boolean'
            ]);
            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }
            $user = Meal::find($request->route('id'))->update(array_merge(
                $validator->validated()
            ));
            return $this->dataResponse('Meal updated successfully', $user);
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    public function remove(Request $request): JsonResponse
    {
        try {
            if(Gate::allows('can:destroy-meal')){
                if(Str::isUuid($request->route('id'))){
                    if(Meal::find($request->route('id'))){
                        $meal = Meal::find($request->route('id'))->delete();
                        return $this->dataResponse("Data deleted successfully", $meal);
                    }else{
                        throw new \Exception("Nothing to delete");
                    }
                }else{
                    throw new \Exception("Invalid id supplied");
                }
            }else {
                throw new \Exception("Forbidden resource");
            }
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }
}
