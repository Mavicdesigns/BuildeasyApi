<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Orders;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomersController extends Controller
{
    public function index(Request $request){

        try{

            $query = Customers::query();
            $allCustomers = $query;

            if(isset($request->page)){
                if(isset($request->limit)){
                    $allCustomers = $query->paginate($request->limit);
                }else{
                    $allCustomers = $query->paginate(10);

                }

            }

            return response()->json([
                "allCustomers" => $allCustomers
            ]);


        }catch(QueryException $ex){

            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }


    }


    public function getCustomersOrders(Request $request){


        if(!isset($request->buyers_id)){
            return response()->json([
                'error' => 1,
                'error_message' => 'No buyer_id specified'
            ],500);
        }else{

            if(!Customers::where('user_id', '=', $request->buyers_id)->exists()){

                return response()->json([
                    'error' => 1,
                    "error_message" => "No Customer found with that id"
                ], 500);

            }else{
                try{

                    $query = Orders::where([
                        ['buyer_id' ,'=', $request->buyers_id],
                        ['status', '<=', 2 ]
                    ]);

                    $orders = $query->get();


                    if(isset($request->page)){
                        if(isset($request->limit)){
                            $orders = $query->paginate($request->limit);

                        }else{
                            $orders = $query->paginate(10);

                        }

                    }

                    return response()->json([
                        'buyers_orders' => $orders
                    ]);

                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ], 500);
                }


            }
        }


    }

    public function getCustomer(Request $request){


        if(!isset($request->customer_id)){
            return response()->json([
                'message' => 'No Customer_id specified'
            ]);
        }else{
            $query = Customers::where('user_id', '=', $request->customer_id);

            if(!$query->exists()){

                return response()->json([
                    'message' => "No customer by this id "
                ]);

            }else{

                try{

                    $customer = $query->paginate(1);


                    return response()->json([
                        'Customer' => $customer
                    ]);

                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }


            }
        }

    }

    public function authenticate(Request $request){

        $data['email'] = $request->json('email');
        $data['password'] = $request->json('password');

        try{
           if(!$token = Auth::guard('api')->attempt($data)){
               return response()->json(['error' => 1,'error_message' => 'User not found', 'extra' => bcrypt($data['password'])], 400);

           }
            return response()->json([
                'message' => 'Authenticated',
                'error' => 0,
                'token' => $token
            ]);


        }catch(JWTException $e){
            return response()->json([
                    'error' => 1,
                    'error_message' => 'could_not_create_token'
                ]
                , 500);

        }





    }
    public function getAuthenticated(Request $request){



        try{
           if(!$user = JWTAuth::parseToken()->authenticate()){
               return response()->json(['error' => 1,'error_message' => 'User not found'], 400);

           }
        }catch(JWTException $e){
            return response()->json([
                    'error' => 1,
                    'error_message' => 'issue with server'
                ]
                , 500);

        }




        return response()->json([
            'message' => 'Authenticated',
            'error' => 0,
            'user' => $user
        ]);



    }

    public function registerCustomer(Request $request){
        try{


            try{
                if(!$user = JWTAuth::parseToken()->authenticate()){
                    return response()->json(['error' => 1,'error_message' => 'User not found'], 400);
                }

                $data = $request->json()->all();

                $name = explode(' ',$user->name,2);

                $customer = Customers::create([
                    'user_id' => $user->user_id,
                    "firstName" => $name[0],
                    "LastName" => $name[1],
                    "phone_number" => $data['phone_number'],
                    "companyName" => $data['companyName'],
                    'community' => $data['locality'],
                    "country" => $data['country'],
                    "city" => $data['city'],
                    "zip" => 0,
                    "address1" => $data['mainAddress'],
                    "address2" => $data['address2'],
                    "local_government" => $data['local_government']
                ]);

                return response()->json([
                    'error' => 0,
                    'success' => 'Customer details registered',
                    'customer' => $customer
                ]);


            }catch(JWTException $e){
                return response()->json([
                        'error' => 1,
                        'error_message' => 'issue with server'
                    ]
                    , 500);

            }



        }catch(\Exception $e){
            return response()->json([
                    'error' => 1,
                    'error_message' => 'issue with server'
                ]
                , 500);

        }
    }

    public function registerUser(Request $request){
        $data = $request->json()->all();




            try{
                $newUser = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'user_id' =>  str_random(7),
                ]);



                return response()->json([
                    'error' => 0,
                    'user' => $newUser
                ]);

            }catch(\Exception $e){
                return response()->json([
                    'error' => 1,
                    'error_message' => $e->getMessage()
                ]);
            }



    }

}
