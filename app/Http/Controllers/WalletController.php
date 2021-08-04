<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('pin', ['except' => ['create', 'show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        try {
            $data = User::find(auth()->user()->id)->wallet;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer FLWSECK_TEST-SANDBOXDEMOKEY-X',
            ])->get("https://api.flutterwave.com/v3/virtual-account-numbers/$data->reference", []);

            $response->throw();
//            $data->balance = $response['amount'] == null ? 0 : $response['amount'];
            return $this->dataResponse('Wallet details fetched successfully', $data );
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
            ]);

            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }
            $user = auth()->user();
            //Get wallet by id
            $wallet = User::find($user->id)->wallet;
            if($wallet['balance'] < $request->input('amount')){
                throw new \Exception("Insufficient funds");
            }
            $random = $this->generateRandomString();
            $response =  Http::withHeaders([
                'Authorization' => 'Bearer FLWSECK_TEST-SANDBOXDEMOKEY-X',
            ])->post("https://api.flutterwave.com/v3/transfers", [
                "account_bank" => $user->bank_code,
                "account_number" => $user->deposit_account,
                "amount" => $request->input('amount'),
                "currency" => "NGN",
                "reference" => $random,
                "debit_currency" => "NGN"
            ]);

            $response->throw();
            if($response['status'] == 'success'){
                //Update wallet
                //Deduct from sender
                $amount = $request->input('amount');
                $wallet->balance = $wallet->balance - $amount;
                $wallet->save();
                return $this->dataResponse('Transfer successfully initialized');
            }
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

}
