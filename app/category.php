<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    //

    protected $fillable = array('name','category_id');

    public static $rules = array('name' => 'required|min:3');

    public function products(){

    return $this->hasMany('Product');
}
}
