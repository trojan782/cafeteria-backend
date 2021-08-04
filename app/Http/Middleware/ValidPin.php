<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ValidPin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!Hash::check($request->input('pin'), auth()->user()->pin)){
            return response()->json(['status' => 'Invalid transaction pin'], 400);
        }
        return $next($request);
    }
}
