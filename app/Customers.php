<?php
/**
 * Created by PhpStorm.
 * User: Anjola
 * Date: 11/3/2018
 * Time: 10:03 AM
 */
namespace App;


use Illuminate\Database\Eloquent\Model;

class Customers extends Model {

    protected $fillable = array('user_id','firstName','LastName','local_government','phone_number','CompanyName','country','city','community','zip','address1','address2');





}