<?php
namespace App\Http\Controllers;use App\Models\Order;use App\Services\FulfillmentService;use Illuminate\Http\Request;
class FulfillmentController extends Controller{
 public function tracking(Order $order){abort_unless(auth()->id()===$order->user_id||auth()->user()?->is_admin,403);$order->load(['items','shippingAddress','payment','fulfillmentEvents']);return view('orders.tracking',compact('order'));}
 public function update(Request $r,Order $order,FulfillmentService $service){$d=$r->validate(['status'=>'required|in:paid,processing,shipped,completed,cancelled','tracking_code'=>'nullable|string|max:100','shipping_carrier'=>'nullable|string|max:100']);try{$service->transition($order,$d['status'],$d['tracking_code']??null,$d['shipping_carrier']??null);}catch(\InvalidArgumentException $e){return back()->withErrors(['status'=>$e->getMessage()]);}return back()->with('success','وضعیت عملیاتی سفارش به‌روزرسانی شد.');}
}
