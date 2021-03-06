<?php

namespace App\Http\Middleware;

use Closure;

class CORS
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
            return $next($request)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'false')
                ->header('Access-Control-Allow-Methods', '*')
                ->header('Access-Control-Allow-Headers', '*');

//            ->header('Access-Control-Allow-Origin: *')
//            ->header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS, FILES')
//            ->header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token')
//            ;
        }
}
