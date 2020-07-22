<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiTokens;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyApiKey
{

    private const ERR_UNAUTHORIZED = [
        'status'=>'error',
        'message'=>'Unauthorized'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Retrieve request header key
        $key = $request->header('key');

        // Validate key
        if($key){
            $verify = ApiTokens::where('api_token',$key)->where('enabled',1)->first();
            if($verify){
                return $next($request);
            }
        }

        // Return unauthorized response if otherwise
        return response()->json(self::ERR_UNAUTHORIZED,401);
    }
}
