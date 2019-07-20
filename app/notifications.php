<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class notifications extends Model
{
    //

    protected $fillable = array('user_id','not_id','type','status','content','target');



}
