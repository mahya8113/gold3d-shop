<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderAddress extends Model { protected $fillable=['order_id','recipient_name','phone','province','city','address','postal_code']; }
