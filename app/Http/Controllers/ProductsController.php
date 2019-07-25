<?php

namespace App\Http\Controllers;

use App\AdminUsers;
use App\category;
use App\products;
use App\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductsController extends Controller
{

    public function index(Request $request){

       $allProducts = products::all();

        if(isset($request->page)){
            if(isset($request->limit)){
                $allProducts = products::paginate(intval($request->limit));
            }else{
                $allProducts = products::paginate(10);

            }

        }

       return response()->json([
           "allProducts" => $allProducts
       ]);

    }

    public function getProductByCategory(Request $request){

        $returnArray = array();

        try{
            $categories = category::all();

            for($i= 0; $i < count($categories);$i++){

                try{
                    $products = products::where('category_id', '=', $categories[$i]['category_id'])->paginate($request->limit ?? 7);

                    $catArray = array(
                        "category" => $categories[$i]['name'],
                        "category_id" => $categories[$i]['category_id'],
                        'data' => $products
                    );

                    $returnArray = array_add($returnArray,$i,$catArray);



                }catch(QueryException $e){
                    return response()->json([
                        'error' => 1,
                        'error_message' => $e->getMessage()
                    ]);
                }
            }


            return response()->json([
                'error' => 0,
                'result' => $returnArray
            ]);


        }catch(QueryException $e){
            return response()->json([
                'error' => 1,
                'error_message' => $e->getMessage()
            ]);
        }

    }
    public function getProductInCategory(Request $request){
        try{

            if(!isset($request->category_name)) {
                $products = products::paginate(intval($request->limit) ?? 10);
            }else{

                $category = category::where('name', '=', $request->category_name);

                if($category->count() <= 0){
                    $products = products::paginate(intval($request->limit) ?? 10);
                }else{
                    $id = $category->first()['category_id'];
                    $products = products::where('category_id','=',$id)->paginate(intval($request->limit) ?? 10);
                }
            }





            return response()->json([
                'error' => 0,
                'allProducts' => $products
            ]);


        }catch(QueryException $e){
            return response()->json([
                'error' => 1,
                'error_message' => $e->getMessage()
            ]);
        }

    }

    public function getCategoryProduct(Request $request){


        $category = category::where('name', '=', $request->category)->first() ;

        $query =  products::where('category_id', '=', $category->category_id);
        $allProducts = $query->get();

        if(isset($request->page)){
            if(isset($request->limit)){
                $allProducts = $query->paginate(intval($request->limit));
            }else{
                $allProducts = $query->paginate(10);

            }

        }

        return response()->json([
            "allProducts" => $allProducts
        ]);

    }

    public function getCurrentId(Request $request){


        if(!isset($request->product_id)){
            return response()->json([
                'message' => 'No product specified'
            ]);
        }else{

            if(!products::where('product_id', '=', $request->product_id)->exists()){

                return response()->json([
                    "message" => "No Product found with that id"
                ]);

            }else{

                try{
                    $query = products::where([
                        ['product_id' ,'=', $request->product_id],
                    ]);

                    $orders = $query->first();


                    return response()->json([
                        'current_product' => $query->first()
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }

            }
        }

    }

    public function updateProduct(Request $request){



        $input = $request->json()->all();

        if(empty($input['supplier_id'])){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }elseif(!Supplier::where('supplier_id', '=', $input['supplier_id'])->exists()){
            return response()->json([
                'message' => 'No supplier found with that id'
            ]);
        }

        if(empty($input['product_id'])){
            return response()->json([
                'message' => 'No product specified'
            ]);
        }elseif(!products::where('product_id', '=', $input['product_id'])->exists()){
            return response()->json([
                'message' => 'No Product found with that id'
            ]);
        }




        $validator = Validator::make($input,products::$rules);

        if($validator->fails()){
            return response()->json([
                'error' => 1,
                'errors' => $validator->errors()
            ]);
        }


        try{

            $product = products::where('product_id', '=', $input['product_id'])
                ->update([
                    'title' => $input['title'],
                    'price' => $input['price'],
                    'images' => json_encode($input['images']),
                    'valuation' => $input['valuation'],
                    'description' => $input['description'],
                    'category_id' => $input['category_id'],
                    'status' => $input['status'],
                    'options' => json_encode($input['options']),
                    'attributes' => json_encode($input['attributes'])
                ]);


            return response()->json([
                'error' => 0,
                'success' => $product
            ]);



        }catch(QueryException $e){
            return response()->json([
                'error' => 1,
                'error_message' => $e->getMessage()
            ]);
        }






    }


    public function getSupplierProduct(Request $request){

        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{
            $supplierProducts = products::where('supplier_id', '=', $request->supplier_id)->get();

            if(products::where('supplier_id', '=', $request->supplier_id)->count() <= 0 ){

                return response()->json([
                    'message' => "No products from this Supplier"
                ]);

            }else{

                if(isset($request->page)){
                    if(isset($request->limit)){
                        $supplierProducts = products::where('supplier_id', '=', $request->supplier_id)->paginate($request->limit);

                    }else{
                        $supplierProducts = products::where('supplier_id', '=', $request->supplier_id)->paginate(10);

                    }

                }

                return response()->json([
                    'supplier_products' => $supplierProducts
                ]);
            }
        }

    }


    public function createProduct(Request $request){

        $input = $request->json()->all();

        if(empty($input['supplier_id'])){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }elseif(!Supplier::where('supplier_id', '=', $input['supplier_id'])->exists()){
            return response()->json([
                'message' => 'No supplier found with that id'
            ]);
        }




        $validator = Validator::make($input, products::$rules);

        if($validator->fails()){

            return response()->json([
                'error' => 1,
                'errors' => $validator->errors()
            ]);
        }

        $newProduccts = products::firstOrCreate([
            'title' => $input['title'],
            'price' => $input['price'],
            'images' => $input['images'],
            'valuation' => $input['valuation'],
            'description' => $input['description'],
            'supplier_id' => $input['supplier_id'],
            'category_id' => $input['category'],
            'product_id' => str_random(10),
            'status' => $input['status'],
            'options' => json_encode($input['allOptions']),
            'attributes' => json_encode($input['allProperties'])
        ]);

        $message = '';
        if($newProduccts){
            $message = "uploaded Product";
        }else{
            $error = " ";
        }

        return response()->json([
            'message' => $message,

        ], 200);







    }

    public function deleteProduct(Request $request){

        $id = $request->product_id;



        try{

            if(!products::where('product_id', '=', $id)->exists()){

                return response()->json([
                    'error' => 1,
                    'success' => 0,
                    'error_message' => 'Current product not found'
                ]);
            }

            $deleteProduct = products::where('product_id',$id)->delete();

            return response()->json([
                'error' =>  0,
                'success' => $deleteProduct
            ]);


        }catch(QueryException $e){
            return response()->json([
                'error' =>  1,
                'error_message' => $e->getMessage()
            ]);
        }




    }

    public function createNewCategory(Request $request){

        if(empty($request->title)){
            return response()->json([
                "error" => 1,
                "error_message" => "type a name to continue"
            ],400);
        }

        $cat = category::create([
            "name" => $request->title,
            "id" => Str::random()
        ]);

        return response()->json([
            "error" => 0,
            "newCategory" => $cat
        ]);


    }

    public function getCategory(){
        $catGory = category::all();

        return response()->json([
            'categories' => $catGory
        ],200);
    }



}
