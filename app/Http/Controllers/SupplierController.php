<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request){

        try{
            $allProducts = Supplier::all();

            if(isset($request->page)){
                if(isset($request->limit)){
                    $allProducts = Supplier::paginate(intval($request->limit));
                }else{
                    $allProducts = Supplier::paginate(10);

                }

            }

            return response()->json([
                "allSuppliers" => $allProducts
            ]);
        }catch(QueryException $ex){

            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }



    }

    public function createSupplier(Request $request){

        $data = $request->json()->all();

        $validator = Validator::make($data,Supplier::$rules);

        if($validator->fails()){
            return response()->json([
                'error' => 1,
                'error_message' => $validator->errors()
            ]);
        }

        try{

            $newSupplier = Supplier::firstOrCreate([
                'supplier_id' => $data['user_id'],
                'title' => $data['title'],
                'avi' => $data['avi'],
                'address' => $data['address'],
                'LG' => $data['LG'],
                'state' => $data['state'],
                'country' => $data['country'],
                'status' => 'inActive',
                'place_id' => $data['place_id']
            ]);

            return response()->json([
                'error' => 0,
                'results' => $newSupplier
            ]);

        }catch(QueryException $e){
            return response()->json([
                'error' => 1,
                'error_meaage' => $e->getMessage()
            ]);
        }

    }


    public function getSupplier(Request $request){


        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{
            $query = Supplier::where('supplier_id', '=', $request->supplier_id);
            $supplier = $query->get();

            if(!$query->exists()){

                return response()->json([
                    'error' => 1,
                    'error_message' => "No Supplier by this id "
                ]);

            }else{

                try{

                    if(isset($request->page)){
                        if(isset($request->limit)){
                            $supplier =$query->paginate($request->limit);

                        }else{
                            $supplier = $query->paginate(10);

                        }

                    }

                    return response()->json([
                        'error' => 0,
                        'supplier' => $supplier
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }
            }
        }

    }
}
