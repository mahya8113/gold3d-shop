<?php
namespace App\Http\Controllers;
use App\Models\{Product,Order,Category};use App\Services\{CartService,ZarinpalService,GoldMarketService};use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;use Illuminate\Support\Str;use App\Models\Coupon;use App\Services\CheckoutPricingService;
class StoreController extends Controller{
 public function home(GoldMarketService $market){$products=Product::with('category:id,name,slug')->where('is_active',true)->latest()->take(12)->get();$categories=Category::whereIn('slug',['rings','necklaces','bracelets','earrings'])->orderByRaw("FIELD(slug,'rings','necklaces','bracelets','earrings')")->get(['id','name','slug']);return view('home',['products'=>$products,'categories'=>$categories,'market'=>$market->dashboard()]);}
 public function products(Request $r){$d=$r->validate(['q'=>'nullable|string|max:80','category'=>'nullable|integer|min:1','min'=>'nullable|integer|min:0','max'=>'nullable|integer|min:0','available'=>'nullable|boolean','sort'=>'nullable|in:featured,price_asc,price_desc,newest']);$products=Product::with('category:id,name,slug')->where('is_active',true)->when($d['q']??null,fn($q,$s)=>$q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('description','like',"%$s%")))->when($d['category']??null,fn($q,$id)=>$q->where('category_id',$id))->when(isset($d['min']),fn($q)=>$q->where('price','>=',$d['min']))->when(isset($d['max']),fn($q)=>$q->where('price','<=',$d['max']))->when(!empty($d['available']),fn($q)=>$q->where('stock','>',0));match($d['sort']??'featured'){ 'price_asc'=>$products->orderBy('price'), 'price_desc'=>$products->orderByDesc('price'), 'newest'=>$products->latest(), default=>$products->orderByDesc('stock')->latest() };$products=$products->paginate(12)->withQueryString();return view('products.index',compact('products'));}
 public function show(Product $product){abort_unless($product->is_active,404);return view('products.show',compact('product'));}
 public function cart(CartService $cart){return view('cart.index',['items'=>$cart->items(),'total'=>$cart->total()]);}
 public function add(Request $r,CartService $cart){$d=$r->validate(['product_id'=>'required|integer|exists:products,id','quantity'=>'required|integer|min:1|max:20']);$cart->add($d['product_id'],$d['quantity']);return back()->with('success','به سبد خرید افزوده شد.');}
 public function remove(int $id,CartService $cart){$cart->remove($id);return back();}
 public function checkout(CartService $cart, CheckoutPricingService $pricing){abort_if($cart->items()->isEmpty(),redirect()->route('cart'));$quote=$pricing->calculate($cart,session('checkout_shipping','courier'),session('checkout_coupon'));return view('checkout.index',['total'=>$quote['total'],'quote'=>$quote,'cartItems'=>$cart->items()]);}
 public function quote(Request $r, CartService $cart, CheckoutPricingService $pricing){
  abort_if($cart->items()->isEmpty(),422,'سبد خرید خالی است.');
  $d=$r->validate(['shipping'=>'required|in:courier,express,pickup','coupon'=>'nullable|string|max:40']);
  $quote=$pricing->calculate($cart,$d['shipping'],$d['coupon']??null);
  if(($d['coupon']??null) && !$quote['coupon']) return response()->json(['message'=>'کد تخفیف معتبر نیست یا شرایط آن برقرار نیست.'],422);
  session(['checkout_shipping'=>$quote['method'],'checkout_coupon'=>$quote['coupon']['code']??null]);
  return response()->json(['subtotal'=>$quote['subtotal'],'discount'=>$quote['discount'],'shipping'=>$quote['shippingAmount'],'tax'=>$quote['tax'],'total'=>$quote['total'],'coupon'=>$quote['coupon']['code']??null]);
 }
 public function place(Request $r,CartService $cart,ZarinpalService $gateway,CheckoutPricingService $pricing){
  $d=$r->validate(['name'=>'required|string|max:120','phone'=>'required|string|max:20','province'=>'nullable|string|max:80','city'=>'nullable|string|max:80','address'=>'required|string|max:1200','postal_code'=>'nullable|string|max:20','shipping'=>'required|in:courier,express,pickup','coupon'=>'nullable|string|max:40']);
  abort_unless($cart->items()->isNotEmpty(),422,'سبد خرید خالی است.');
  $order=DB::transaction(function()use($d,$cart,$pricing){
    $items=$cart->items()->lockForUpdate(); abort_if($items->isEmpty(),422,'سبد خرید خالی است.');
    $subtotal=(int)$items->sum(fn($p)=>$p->price*$p->cart_quantity);
    $method=array_key_exists($d['shipping'],CheckoutPricingService::SHIPPING)?$d['shipping']:'courier';
    $shipping=CheckoutPricingService::SHIPPING[$method]; $discount=0; $coupon=null;
    if(!empty($d['coupon'])){
      $coupon=Coupon::where('code',strtoupper(trim($d['coupon'])))->where('is_active',true)->lockForUpdate()->first();
      $valid=$coupon && (!$coupon->starts_at || now()->gte($coupon->starts_at)) && (!$coupon->expires_at || now()->lte($coupon->expires_at)) && (!$coupon->usage_limit || ($coupon->used_count+$coupon->reserved_count)<$coupon->usage_limit) && $subtotal >= $coupon->min_order;
      if(!$valid) $coupon=null;
      else $discount=$coupon->type==='percent'?(int)floor($subtotal*$coupon->value/100):min($subtotal,$coupon->value);
    }
    $tax=0; $total=max(0,$subtotal-$discount+$shipping+$tax);
    foreach($items as $p){
      $qty=(int)$p->cart_quantity;
      $updated=Product::whereKey($p->id)->whereRaw('(stock - reserved_stock) >= ?',[$qty])->increment('reserved_stock',$qty);
      abort_unless($updated===1,422,'موجودی کافی نیست: '.$p->name);
    }
    if($coupon) $coupon->increment('reserved_count');
    $o=Order::create(['user_id'=>auth()->id(),'customer_name'=>$d['name'],'phone'=>$d['phone'],'address'=>$d['address'],'subtotal_amount'=>$subtotal,'discount_amount'=>$discount,'shipping_amount'=>$shipping,'tax_amount'=>$tax,'total_amount'=>$total,'shipping_method'=>$method,'coupon_code'=>$coupon?->code,'coupon_id'=>$coupon?->id,'status'=>'pending','invoice_number'=>'G3D-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),'reservation_expires_at'=>now()->addMinutes(20)]);
    $o->shippingAddress()->create(['recipient_name'=>$d['name'],'phone'=>$d['phone'],'province'=>$d['province']??null,'city'=>$d['city']??null,'address'=>$d['address'],'postal_code'=>$d['postal_code']??null]);
    foreach($items as $p){$o->items()->create(['product_id'=>$p->id,'product_name'=>$p->name,'unit_price'=>$p->price,'quantity'=>$p->cart_quantity]);}
    return $o;
  });
  session()->forget(['checkout_shipping','checkout_coupon']); return $gateway->request($order);
 }
 public function invoice(Order $order){abort_unless($order->user_id===auth()->id() || auth()->user()->is_admin,403);$order->load('items','shippingAddress','payment');return view('orders.invoice',compact('order'));}
 public function callback(Request $r,Order $order,ZarinpalService $gateway){return $gateway->verify($r,$order);}
 public function profile(){return view('profile.index',['orders'=>auth()->user()->orders()->with('items')->latest()->paginate(10)]);}
}
