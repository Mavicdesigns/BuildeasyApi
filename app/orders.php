<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Orders extends Model
{
    use Notifiable;
    //

    protected $fillable = array('title','delivery','distance_matrix','total_price','order_id','product_id','buyer_id','token','supplier_id','buyer','price','quantity','valuation','status','destination' );

    public static  $rules = array(
        'buyer' => 'required',
        'buyer_id' => 'required',
        'supplier_id' => 'required',
        'product_id' => 'required',
        //'token' => 'required',
        'quantity' => 'required',
        'price' => 'required',
        'destination' => 'required'
        );



}
