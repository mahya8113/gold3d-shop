<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class Order extends Model{protected $fillable=['user_id','customer_name','phone','address','total_amount','subtotal_amount','discount_amount','shipping_amount','tax_amount','shipping_method','coupon_code','coupon_id','status','tracking_code','invoice_number','paid_at','reservation_expires_at'];protected $casts=['total_amount'=>'integer','paid_at'=>'datetime','reservation_expires_at'=>'datetime'];public function user(){return $this->belongsTo(User::class);}public function items(){return $this->hasMany(OrderItem::class);}public function coupon(){return $this->belongsTo(Coupon::class);}
public function payment(){return $this->hasOne(Payment::class);}public function shippingAddress(){return $this->hasOne(OrderAddress::class);}
public function fulfillmentEvents(){return $this->hasMany(OrderFulfillmentEvent::class)->orderBy('occurred_at');}}
