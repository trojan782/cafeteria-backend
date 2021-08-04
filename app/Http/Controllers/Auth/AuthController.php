<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        if(! $token = auth()->attempt($validator->validated())){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }


    /**
     * Register a User.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|between:2,100',
                'email' => 'required|string|between:2,100',
                'password' => 'required|string|between:7,20',
                'bvn' => 'required|string',
                'pin' => 'required|numeric|digits:6',
            ]);

            if($validator->fails()){
                return response()->json(json_decode($validator->errors()->toJson()), 400);
            }

            $data = User::create(array_merge(
                $validator->validated(),
                [
                    'pin' =>  Hash::make($request->input('pin')),
                    'password' => bcrypt($request->input('password')),
                ]
            ));

            $data->sendEmailVerificationNotification();

            //Create virtual account
            $response =  Http::withHeaders([
                'Authorization' => 'Bearer FLWSECK_TEST-SANDBOXDEMOKEY-X',
            ])->post('https://api.flutterwave.com/v3/virtual-account-numbers', [
                "email" => $request->input('email'),
                "is_permanent" => true,
                "bvn" =>  $request->input('bvn'),
                "tx_ref" =>  $this->generateRandomString(),
            ]);

            $response->throw();
            //Create wallet
            Wallet::create([
                'balance' => 0,
                'user_id' => $data->id,
                'visibility' => true,
                'reference' => $response['data']['order_ref'],
                'virtual_account' => $response['data']['account_number'],
            ]);
            return response()->json([
                'message' => 'User successfully registered',
                'user' => $data
            ], 201);
        } catch (\Exception $e){
            return $this->dataResponse($e->getMessage(), null, 'error');
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function userProfile(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function createNewToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
