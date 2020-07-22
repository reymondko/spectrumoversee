<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class isSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( Auth::user()->my_role->title != 'Super Admin' ) {
            return abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
