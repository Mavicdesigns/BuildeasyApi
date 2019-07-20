<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    //

    protected $fillable = array('title','category_id','status','attributes','options','product_id','supplier_id','valuation','description','price','availability','images');

    protected $casts = [
        'attributes' => 'array',
        'options' => 'array',
        'images' => 'array'
    ];

    public static  $rules = array(
        'category_id' => 'required',
        // 'product_id' => 'required|min:2',
         'supplier_id' => 'required',
      //  'quantity' => 'required|numeric',
        'valuation' => 'required',
        'title' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'status' => 'required',
        //'image' => 'mimes:jpeg,jpg,png '
        );


    public function Category(){
        return $this->belongsTo('Category');
    }

}
