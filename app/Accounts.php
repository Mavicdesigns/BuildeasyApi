<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    //

    protected $fillable = ['account_id','supplier_id','order_id','status','price'];

}
