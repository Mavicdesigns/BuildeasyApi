<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //

    protected $fillable = array('title','avi','supplier_id','place_id','LG','state','status','country','address');

    public static  $rules = array(
        'user_id' => 'required',
        'LG' => 'required',
        'state' => 'required',
        'status' => 'required',
        'title' => 'required',
        'country' => 'required',
        'address' => 'required',
        );




}
