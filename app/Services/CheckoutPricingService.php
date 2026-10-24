<?php
namespace App\Services;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
class CheckoutPricingService {
 public const SHIPPING = ['courier'=>85000,'express'=>160000,'pickup'=>0];
 public function calculate(CartService $cart, ?string $shipping='courier', ?string $couponCode=null): array {
  $items=$cart->items(); $subtotal=$cart->total();
  $method=array_key_exists($shipping ?? 'courier',self::SHIPPING)?($shipping ?? 'courier'):'courier';
  $shippingAmount=self::SHIPPING[$method]; $discount=0; $coupon=null;
  if($couponCode){$coupon=Coupon::where('code',strtoupper(trim($couponCode)))->where('is_active',true)->first();
   if($coupon && (!$coupon->starts_at || now()->gte($coupon->starts_at)) && (!$coupon->expires_at || now()->lte($coupon->expires_at)) && (!$coupon->usage_limit || $coupon->used_count<$coupon->usage_limit) && $subtotal >= $coupon->min_order){
    $discount=$coupon->type==='percent' ? (int)floor($subtotal*$coupon->value/100) : min($subtotal,$coupon->value);
   } else {$coupon=null;}
  }
  $tax=0; $total=max(0,$subtotal-$discount+$shippingAmount+$tax);
  return compact('subtotal','discount','shippingAmount','tax','total','method','coupon');
 }
}
