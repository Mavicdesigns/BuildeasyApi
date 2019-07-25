<?php

namespace App\Http\Controllers;

use App\Customers;
use App\Imaging;
use App\Imagings;
use App\products;
use App\Supplier;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JD\Cloudder\Facades\Cloudder;
use PHPUnit\Runner\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Whoops\Exception\ErrorException;

class ApiController extends Controller
{

    var $google_base_Url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    var $api_key = 'AIzaSyCoHhq-078QaLuiSUWMyBhT-DbXhHLHjwA';

    public function index(){

    }

    public function getDistance(Request $request){

        $input = $request->json()->all();

        $origin = $input['origin'];
        $des = $input['des'];
        $url = $this->google_base_Url.'?origin='.$origin.'&destinations='.$des.'&mode=driving$language=en&key='.$this->api_key;

        $response =  file_get_contents($this->google_base_Url.'?origins='.$origin.'&destinations='.$des.'&mode=Driving&language=fr-FR&key='.$this->api_key);

        return response()->json([
            'result' => json_decode($response)
        ]);
    }

    public function getCloserSupplier(Request $request){
        $data = $request->json()->all();

        $api_key =  env('googleKey');


        $supplier = Supplier::all();
        $responceArray = array();

        for($i = 0; $i < count($supplier);$i++){
           try{
               $responce = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=place_id:'.$supplier[$i]['place_id'].'&destinations='.$data['destination'].'&mode=Driving&language=fr-FR&key='.$api_key);

           }catch(\Exception $e){
               return response()->json([
                   'error' => 1,
                   'error_message' => $e->getMessage(),
               ]);
           }
            $distance = json_decode($responce, true);
            $value = $distance['rows'][0]['elements'][0]['duration']['value'] ?? '';
            $valueArray  = array('distance' => $value,'data' => $distance,'supplier_details' => $supplier[$i]);

            $responceArray = array_add($responceArray,$i,$valueArray);
        }


        return response()->json([
            'results' => min($responceArray)
        ]);

    }

    public function uploadSingleFile($file){

        $publicId =  "BuildeasyFiles/".$file->getClientOriginalName(). Str::random(16);

        Cloudder::upload($file->getRealPath(), $publicId);




        return Cloudder::secureShow($publicId);

    }

    public function UploadImage(Request $request){

        if(isset($request->supplier_id) && Supplier::where('supplier_id', '=', $request->supplier_id)->exists()) {

            $supplier_id = $request->supplier_id;




            if ($request->hasFile('image')) {

                $data = array();

                $count = 0;

                foreach ($request->file('image') as $file) {

                    $name = $file->getClientOriginalName();


                    try {


                        $src = $this->uploadSingleFile($file);
                        $id = Str::random(20);


                        $image = Imagings::firstOrCreate([
                            'image_id' => $id,
                            'src' => $src,
                            'title' => $name,
                            'supplier_id' => $supplier_id

                        ]);

                        $imageData = [
                            'id' => $id,
                            'src' => $src,
                            'alt' => $name
                        ];


                        $data = array_add($data, $count, $imageData);

                        $count++;

                    } catch (\Exception $e) {
                        return response()->json([
                                'error' => 0,
                                'data' => $e->getMessage(),
                                'error_message' => "There was a problem Updating the image". $e->getMessage()
                            ]
                        );
                    }

                }

                return response()->json([
                        'error' => 0,
                        'images' => $data,
                        'count' => $count
                    ]
                );


            }else{
                return response()->json([
                    'error' => 1,
                    'error_message' => 'Unable to find Image'
                ]);
            }


        }else{

            return response()->json([
                'error' => 1,
                'error_message' => 'Error Authenticating User'
            ]);
        }

    }

    public function createUser(Request $request){
        $data = $request->json()->all();

        $validate = Validator::make($data, User::$rules);


        if(!$validate->fails()){
            try{

                $newUser = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => ($data['password']),
                    'user_id' =>  str_random(7),
                ]);

                return response()->json([
                    "error" => 0,
                    "user" => $newUser
                ]);


            }catch(\Exception $e){

                return response()->json([
                    'error' => 0,
                    "error_message" => $e->getMessage()
                ],500);
            }
        }else{
            return response()->json([
                'error' => 0,
                "error_message" => $validate->errors()
            ],500);
        }
    }

    public function getSupplierImages(Request $request){

        if(isset($request->supplier_id) && Supplier::where('supplier_id', '=', $request->supplier_id)->exists()) {

            try{

                $image = Imagings::where('supplier_id', '=', $request->supplier_id)->get();

                return response()->json([
                    'error' => 0,
                    'data' => $image
                ]);

            }catch(\Exception $e){
                return response()->json([
                        'error' => 0,
                        'data' => $e->getMessage(),
                        'error_message' => "There was a problem Updating the image"
                    ]
                );
            }

        }else{

            return response()->json([
                'error' => 1,
                'error_message' => 'Error Authenticating User'
            ]);

        }


    }


}
