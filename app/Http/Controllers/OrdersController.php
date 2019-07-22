<?php

namespace App\Http\Controllers;

use App\Accounts;
use App\Customers;
use App\notifications;
use App\Notifications\OrderPaid;
use App\Orders;
use App\products;
use App\Supplier;
use App\User;
use Faker\Provider\DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    public function index(Request $request){

        try{

            $order = Orders::paginate(10);

            return response()->json([
                "error" => 0,
                "orders" => $order
            ]);


        }catch(QueryException $e){
            return response()->json([
                "error" => 1,
                "error_message" => $e->getMessage()
            ]);
        }

    }
    public function supplierOrders(Request $request){

        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{
            $query = Supplier::where('supplier_id', '=', $request->supplier_id);


            if(!$query->exists()){

                return response()->json([
                    "message" => "No Supplier found with that id"
                ]);

            }else{


                try{
                    $orders = Orders::where('supplier_id', '=', $request->supplier_id);

                    if(isset($request->page)){
                        if(isset($request->limit)){
                            $orders = $orders->paginate($request->limit);

                        }else{
                            $orders = $orders->paginate(10);

                        }

                    }else{
                        $orders = $orders->paginate(10);

                    }

                    return response()->json([
                        'supplier_orders' => $orders
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }

            }
        }

    }


    public function file_post_contents($url, $params) {


        $content = json_encode($params, true);
        $header = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: ".strlen($content)
        );
        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => $content,
                'header' => implode("\r\n", $header)
            )

        );
        return file_get_contents($url, false, stream_context_create($options));
    }


    public function generateRandomNumber($len = 16) {
        $char = 'abc1def4g25OPQR098ST3U7ffdVW8XYZ9';
        $randomNumber = '';
        for ($i = 0; $i < $len; $i++) {
            $randomNumber .= $char[rand(0, $len - 1)];
        }
        return $randomNumber;

    }

    public  function createOrder(Request $request){

        $data = $request->json()->all();

        $validator = Validator::make($data, Orders::$rules);

        if($validator->fails()){
            return response()->json([
                'error' => 1,
                'error_message' => $validator->errors()
            ]);
        }else {

            try {

                $product = products::where('product_id', '=', $data['product_id']);

                if ($product->exists()) {

                    $product = $product->first();

                    $url = 'http://localhost/buildeasyApi/public/api/getCloserSupplier?api_key=4ntbqhy2g0mc';
                    $requestData = array(
                        "destination" => $data['destination'],
                        "product_name" => $product['title']
                    );




                    $response = $this->file_post_contents($url, $requestData);

                    $result = json_decode($response, true);

                    if(!empty($result['results'])){

                       try{


                           $order = Orders::where([
                               ['product_id','=',$product['product_id']],
                               ['buyer_id','=',$data['buyer_id']],
                               ['destination','=',$data['destination']],
                               ['status', '=', 0 ]
                           ])->first();

                           if($order){
                             return response()->json([
                                 'error' => 1,
                                 'error_message' => 'this order '.$order->title.' is still been proccesed'
                             ]);
                           }else{
                               $newOrder = Orders::firstOrCreate([
                                   'order_id' => $this->generateRandomNumber(15),
                                   'buyer' => $data['buyer'],
                                   'buyer_id' => $data['buyer_id'],
                                   'distance_matrix' => $result['results']['data']['rows'][0]['elements'][0]['distance']['value'],
                                   'supplier_id' => $result['results']['supplier_details']['supplier_id'],
                                   'product_id' => $product['product_id'],
                                   'token' => $this->generateRandomNumber(20),
                                   'total_price' => (intval($data['price']) * $data['quantity']),
                                   'quantity' => $data['quantity'],
                                   'price' => $data['price'],
                                   'valuation' => $product['valuation'],
                                   'title' => $this->generateRandomNumber(5),
                                   'status' => 0,
                                   'destination' => $data['destination']
                               ]);

                               $user = User::where('user_id', '=', $newOrder['supplier_id'])->get();

                               $details = [
                                   'greeting' => 'Hi'.$user['company Name'],
                                   'body' => 'One order is need your assistance immediately'
                               ];

                               Notification::send($user, new OrderPaid($details));

                               return response()->json([
                                   'error' => 0,
                                   'data' => $newOrder,
                                   'matrix' => $result['results']

                               ]);
                           }





                       }catch(QueryException $e){
                          return response()->json([
                               'error' => 1,
                               'error-message' => $e->getMessage()
                           ]);
                       }


                    }else{
                        return response()->json([
                            'error' => 1,
                            'error_message' => 'could not  connect. Try again later'
                        ]);
                    }


                } else {


                    return response()->json([
                        'error' => 1,
                        'error_message' => 'Unable to find product'
                    ]);
                }


            } catch (QueryException $e) {
                return response()->json([
                    'error' => 1,
                    'error_message' => $e->getMessage()
                ]);
            }
        }



    }


    public function unverifiedOrders(Request $request){


        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{

            if(!Supplier::where('supplier_id', '=', $request->supplier_id)->exists() ){

                return response()->json([
                   "message" => "No Supplier found with that id"
                ]);

            }else{

                try{

                    $query = Orders::where([
                        ['supplier_id' ,'=', $request->supplier_id],
                        ['status' ,'=', 0 ]
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
                        'unverified_orders' => $orders
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }


            }
        }


    }
    public function processedOrders(Request $request){


        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{

            if(!Supplier::where('supplier_id', '=', $request->supplier_id)->exists()){

                return response()->json([
                   "message" => "No Supplier found with that id"
                ]);

            }else{

                try{

                    $query = Orders::where([
                        ['supplier_id' ,'=', $request->supplier_id],
                        ['status' ,'=', 1 ]
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
                        'processed_orders' => $orders
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }


            }
        }

    }
    public function awaitingDelivery(Request $request){


        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{

            if(!Supplier::where('supplier_id', '=', $request->supplier_id)->exists()){

                return response()->json([
                   "message" => "No Supplier found with that id"
                ]);

            }else{

                try{

                    $query = Orders::where([
                        ['supplier_id' ,'=', $request->supplier_id],
                        ['status' ,'=', 2 ]
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
                        'awaiting_orders' => $orders
                    ]);

                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }

            }
        }

    }
    public function completedOrders(Request $request){


        if(!isset($request->supplier_id)){
            return response()->json([
                'message' => 'No supplier_id specified'
            ]);
        }else{

            if(!Supplier::where('supplier_id', '=', $request->supplier_id)->exists()){

                return response()->json([
                   "message" => "No Supplier found with that id"
                ]);

            }else{
                try{

                    $query = Orders::where([
                    ['supplier_id' ,'=', $request->supplier_id],
                    ['status' ,'=', 3 ]
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
                        'completed_orders' => $orders
                    ]);

                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }

            }
        }

    }
    public function getSingleOrder(Request $request){


        if(!isset($request->order_id)){
            return response()->json([
                'error' => 1,
                'error_message' => 'No order_id specified'
            ]);
        }else{

            if(!Orders::where('order_id', '=', $request->order_id)->exists()){

                return response()->json([
                    'error' => 1,
                   "error_message" => "No Order found with that id"
                ]);

            }else{

                try{
                    $query = Orders::where([
                        ['order_id' ,'=', $request->order_id],
                    ]);

                    $orders = $query->first();


                    return response()->json([
                        'error' => 0,
                        'current_order' => $orders,
                        'product' => products::where('product_id','=',$orders['product_id'])->first(),
                        'customer' => Customers::where('user_id','=', $orders['buyer_id'])->first()
                    ]);
                }catch(QueryException $ex){

                    return response()->json([
                        'error' => $ex->getMessage()
                    ]);
                }

            }
        }

    }

    public function acceptOrder(Request $request){

        if(!Orders::where('order_id', '=', $request->order_id)->exists()){

            return response()->json([
                'error' => 1,
                "message" => "No Order found with that id"
            ]);

        }else {

           try{

               $Updateorder = Orders::where([
                   ['order_id', '=',  $request->order_id],
               ])->update(['status' => 2]);

               $order = Orders::where([
                   ['order_id', '=',  $request->order_id],
                   ['status', '=', 2 ]

               ])->first();

               notifications::firstOrCreate([
                   'user_id' => $order['supplier_id'],
                   'not_id' => str_random(10),
                   'type' => 'message',
                   'content' => 'Customer has accepted this order please deliver as quick as possible',
                   'status' => 'Unread',
                   'target' => 'Orders/confirm/'.$order['order_id'],
               ]);

               return response()->json([
                   'error' => 0,
                   'order' => $order
               ]);
           }catch(\Exception $e){
               return response()->json([
                   "error" => 1,
                   "error_message" => $e->getMessage()
               ]);
           }

        }


    }

    public function deliverOrder(Request $request){
        if(!Orders::where('order_id', '=', $request->order_id)->exists()){

            return response()->json([
                'error' => 1,
                "message" => "No Order found with that id"
            ]);

        }else {

            try{

                $Updateorder = Orders::where([
                    ['order_id', '=',  $request->order_id],
                ])->update(['status' => 1]);


                return response()->json([
                    'error' => 0,
                ]);
            }catch(\Exception $e){
                return response()->json([
                    "error" => 1,
                    "error_message" => $e->getMessage()
                ]);
            }

        }

    }

    public function verifyToken(Request $request){

        if(!Orders::where('order_id', '=', $request->order_id)->exists()){

            return response()->json([
                'error' => 1,
                "message" => "No Order found with that id"
            ]);

        }else{

            $input = $request->json()->all();

            if(empty($input['token'])){
                return response()->json([
                    'error' => 1,
                    'error_message' => 'no token Specified'
                ]);
            }else{

                $order = Orders::where([
                    ['order_id', '=', $request->order_id],
                    ['status', '=', 2]
                ])->latest()->first();

                if($input['token'] === $order['token']){


                    try{
                        $order->update(['status' => 3]);


                        Accounts::firstOrCreate([

                            "account_id" =>  $this->generateRandomNumber(7),
                            "supplier_id" =>  $order->supplier_id,
                            "order_id" => $order->order_id,
                            "status" => "Not Paid",
                            "price" => $order->price

                        ]);


                        return response()->json([
                            'error' => 0,
                            'message' => 'Order Completed',
                            'order' => $order
                        ]);

                    }catch(QueryException $ex){

                        return response()->json([
                            'error' => 1,
                            'error_message' => $ex->getMessage()
                        ]);
                    }
                }else{
                    return response()->json([
                        'error' => 1,
                        'error_message' => 'Invalid token'
                    ]);
                }



            }


        }


    }

    public function rejectOrder(Request $request){


        if(!isset($request->order_id)){
            return response()->json([
                "message" => "No Order id Specified"
            ]);
        }

        $query = Orders::where('order_id', '=', $request->order_id);


        if(!$query->exists()){

            return response()->json([
                "message" => "No Order found with that id"
            ]);

        }else{

            try{

                $supplier = Supplier::inRandomOrder()->first();

                $query->update([
                    'supplier_id' => $supplier->supplier_id
                ]);

                if($query){
                    return response()->json([
                        "message" => "Order rejected"
                    ]);
                }else{
                    return response()->json([
                        "message" => "Unable to delete"
                    ]);
                }


            }catch(QueryException $ex){
                return response()->json([
                    "error" => $ex->getMessage()
                ]);
            }





        }



    }



}
