<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZarinpalService
{
    public function request(Order $order)
    {
        $merchantId = trim((string) config('zarinpal.merchant_id'));
        $placeholderMerchant = $merchantId === '' || $merchantId === '00000000-0000-0000-0000-000000000000';
        if ($placeholderMerchant) {
            $this->releaseReservation($order, 'payment_not_configured');
            return redirect()->route('payment.failed')->with('error', 'Merchant ID زرین‌پال در فایل .env تنظیم نشده است.');
        }

        if (!$order->payment) {
            $order->payment()->create([
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);
        }

        try {
            $response = Http::connectTimeout(5)->timeout(15)->post(config('zarinpal.api_url') . '/request.json', [
            'merchant_id' => $merchantId,
            'amount' => (int) $order->total_amount * 10,
            'callback_url' => route('payment.callback', $order),
            'description' => 'Gold3D order #' . $order->id,
            'metadata' => ['mobile' => $order->phone],
            ]);
        } catch (\Throwable $e) {
            Log::warning('ZarinPal request connection failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);
            $this->releaseReservation($order, 'gateway_connection_failed');
            return redirect()->route('payment.failed')->with('error', 'اتصال به درگاه پرداخت برقرار نشد.');
        }

        $data = $response->json('data', []);
        $code = (int) ($data['code'] ?? 0);

        if (!$response->successful() || $code !== 100 || empty($data['authority'])) {
            Log::warning('ZarinPal request failed', ['status' => $response->status(), 'data' => $data]);
            $this->releaseReservation($order, 'gateway_request_failed');
            return redirect()->route('payment.failed')->with('error', 'درخواست پرداخت زرین‌پال ناموفق بود.');
        }

        $order->payment()->update([
            'authority' => $data['authority'],
            'amount' => (int) $order->total_amount,
            'status' => 'pending',
        ]);
        session()->forget('cart');

        return redirect(config('zarinpal.startpay_url') . '/' . $data['authority']);
    }

    public function verify(Request $request, Order $order)
    {
        $result = DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $lockedOrder->payment()->lockForUpdate()->first();
            if ($lockedOrder->status === 'paid' && $payment?->status === 'paid') return ['ok'=>true,'ref_id'=>$payment->ref_id];
            if (!$payment || !hash_equals((string)$payment->authority,(string)$request->query('Authority'))) {
                return ['ok'=>false,'ref_id'=>null,'release'=>false];
            }
            if ($payment->status !== 'pending' || $lockedOrder->status !== 'pending') {
                return ['ok'=>false,'ref_id'=>null,'release'=>false];
            }
            if (!$lockedOrder->reservation_expires_at || $lockedOrder->reservation_expires_at->isPast()) {
                $this->releaseReservationLocked($lockedOrder, 'reservation_expired_before_callback');
                return ['ok'=>false,'ref_id'=>null,'release'=>true];
            }
            if ($request->query('Status') !== 'OK') {
                $this->releaseReservationLocked($lockedOrder, 'gateway_cancelled');
                return ['ok'=>false,'ref_id'=>null,'release'=>true];
            }
            if ((int)$payment->amount !== (int)$lockedOrder->total_amount) {
                Log::warning('Payment amount mismatch',['order_id'=>$lockedOrder->id]);
                $this->releaseReservationLocked($lockedOrder);
                return ['ok'=>false,'ref_id'=>null,'release'=>true];
            }
            try {
                $resp=Http::connectTimeout(5)->timeout(15)->post(config('zarinpal.api_url').'/verify.json',[
                    'merchant_id'=>trim((string) config('zarinpal.merchant_id')),'amount'=>(int)$payment->amount*10,'authority'=>$payment->authority,
                ]);
            } catch (\Throwable $e) {
                Log::warning('ZarinPal verification connection failed',['order_id'=>$lockedOrder->id,'message'=>$e->getMessage()]);
                return ['ok'=>false,'ref_id'=>null,'release'=>false];
            }
            $data=$resp->json('data',[]); $code=(int)($data['code']??0);
            if(!$resp->successful() || !in_array($code,[100,101],true)){
                Log::warning('ZarinPal verification failed',['status'=>$resp->status(),'data'=>$data,'order_id'=>$lockedOrder->id]);
                $this->releaseReservationLocked($lockedOrder);
                return ['ok'=>false,'ref_id'=>null,'release'=>true];
            }
            $payment->update(['ref_id'=>(string)($data['ref_id']??''),'status'=>'paid']);
            foreach($lockedOrder->items as $item){
                $updated=\App\Models\Product::whereKey($item->product_id)->where('reserved_stock','>=',$item->quantity)->lockForUpdate()->decrement('reserved_stock',$item->quantity);
                if($updated!==1){Log::error('Reserved stock mismatch on payment',['order_id'=>$lockedOrder->id,'product_id'=>$item->product_id]); throw new \RuntimeException('خطای موجودی در تأیید پرداخت.');}
                \App\Models\Product::whereKey($item->product_id)->decrement('stock',$item->quantity);
            }
            if($lockedOrder->coupon_id){
                $coupon=\App\Models\Coupon::whereKey($lockedOrder->coupon_id)->lockForUpdate()->first();
                if($coupon){$coupon->decrement('reserved_count');$coupon->increment('used_count');}
            }
            $lockedOrder->update(['status'=>'paid','paid_at'=>now(),'reservation_expires_at'=>null]);
            return ['ok'=>true,'ref_id'=>$payment->ref_id];
        });
        return $result['ok']?redirect()->route('payment.success')->with('ref_id',$result['ref_id']):redirect()->route('payment.failed');
    }

    public function releaseReservation(Order $order, string $reason='manual'): void
    { DB::transaction(function() use ($order,$reason){$locked=Order::with('items')->whereKey($order->id)->lockForUpdate()->first(); if($locked) $this->releaseReservationLocked($locked,$reason);}); }

    public function releaseReservationLocked(Order $order, string $reason='payment_failed'): void
    {
        // Idempotent release: a cancelled/failed reservation must never be released twice.
        if(in_array($order->status,['paid','payment_failed'],true) || is_null($order->reservation_expires_at)) return;
        foreach($order->items as $item){
            \App\Models\Product::whereKey($item->product_id)->lockForUpdate()->decrement('reserved_stock',min($item->quantity,(int)\App\Models\Product::whereKey($item->product_id)->value('reserved_stock')));
        }
        if($order->coupon_id){$coupon=\App\Models\Coupon::whereKey($order->coupon_id)->lockForUpdate()->first(); if($coupon && $coupon->reserved_count>0)$coupon->decrement('reserved_count');}
        $order->update(['status'=>'payment_failed','reservation_expires_at'=>null]);
        Log::info('Order reservation released',['order_id'=>$order->id,'reason'=>$reason]);
    }

}
