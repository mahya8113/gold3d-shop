<?php
namespace App\Services;
use App\Models\{Order,OrderFulfillmentEvent};use Illuminate\Support\Str;
class FulfillmentService{
 public function transition(Order $order,string $status,?string $tracking=null,?string $carrier=null):Order{
  $allowed=[
   'pending'=>['paid','cancelled'],
   'paid'=>['processing'],
   'processing'=>['shipped','cancelled'],
   'shipped'=>['completed'],
   'completed'=>[],
   'cancelled'=>[],
  ];
  if(!in_array($status,$allowed[$order->status]??[],true)){
   throw new \InvalidArgumentException('انتقال وضعیت سفارش مجاز نیست.');
  }
  if(in_array($status,['shipped','completed'],true) && $order->status!=='shipped' && $status==='completed'){
   throw new \InvalidArgumentException('تحویل فقط پس از ارسال مجاز است.');
  }
  if($status==='paid' && $order->payment?->status !== 'paid'){
   throw new \InvalidArgumentException('سفارش فقط پس از تأیید پرداخت می‌تواند paid شود.');
  }
  $map=['pending'=>['در انتظار پرداخت','سفارش ایجاد شد.'],'paid'=>['پرداخت تأیید شد','پرداخت سفارش با موفقیت تأیید شد.'],'processing'=>['در حال آماده‌سازی','سفارش وارد مرحله کنترل کیفیت و بسته‌بندی شد.'],'shipped'=>['تحویل شرکت حمل','سفارش تحویل شرکت حمل شد.'],'completed'=>['تحویل مشتری','سفارش با موفقیت تحویل داده شد.'],'cancelled'=>['لغو شده','سفارش لغو شد.']];
  $order->status=$status;if($tracking!==null)$order->tracking_code=$tracking;if($carrier!==null)$order->shipping_carrier=$carrier;
  if($status==='shipped')$order->shipped_at=now();if($status==='completed'){ $order->delivered_at=now(); if(!$order->certificate_serial)$order->certificate_serial='G3D-'.now()->format('Ym').'-'.strtoupper(Str::random(8)); }
  $order->save();[$title,$desc]=$map[$status]??['به‌روزرسانی سفارش','وضعیت سفارش تغییر کرد.'];OrderFulfillmentEvent::create(['order_id'=>$order->id,'status'=>$status,'title'=>$title,'description'=>$desc,'actor'=>auth()->user()?->name ?? 'system','occurred_at'=>now()]);return $order->fresh();
 }
}
