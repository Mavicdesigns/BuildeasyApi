<?php

namespace App\Http\Middleware;

use App\AdminUsers;
use Closure;
use Illuminate\Routing\SortedMiddleware;

class CheckApiKey
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
        if ($request->api_key == '') {

            return response()->json([
                'message' => "No api_key defined"
            ]);


        } else {


            $users = AdminUsers::where('api_key', $request->api_key)->count();

            if ($users != 1) {

                return response()->json([
                    'message' => "Invalid access key"
                ]);



            } else {


                return $next($request)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization');


            }
        }
    }
}
