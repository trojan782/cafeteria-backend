<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = User::find(auth()->user()->id)->wallet;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer FLWSECK_TEST-SANDBOXDEMOKEY-X',
            ])->get("https://api.flutterwave.com/v3/virtual-account-numbers/$data->reference", []);

            $response->throw();
            $data->balance = $response['amount'] == null ? 0 : $response['amount'];
            return $this->dataResponse('Wallet details fetched successfully', $data );
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function withdraw(): Response
    {
        //
    }

}
