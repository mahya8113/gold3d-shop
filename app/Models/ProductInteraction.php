<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductInteraction extends Model { protected $fillable=['user_id','session_key','product_id','event','weight','meta']; protected $casts=['meta'=>'array']; }
