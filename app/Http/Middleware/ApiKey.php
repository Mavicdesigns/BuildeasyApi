<?php

namespace App\Http\Middleware;

use App\AdminUsers;
use Closure;

class ApiKey
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
                ],403);



            } else {


                return $next($request);

            }
        }
    }
}
