<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\ZarinpalService;
class ReleaseExpiredReservations extends Command { protected $signature='orders:release-expired-reservations'; protected $description='Release expired pending order reservations'; public function handle(ZarinpalService $service): int { $orders=Order::where('status','pending')->whereNotNull('reservation_expires_at')->where('reservation_expires_at','<',now())->pluck('id'); foreach($orders as $id){$service->releaseReservation(Order::findOrFail($id),'reservation_expired');} $this->info('Released '.$orders->count().' reservation(s).'); return self::SUCCESS; }}
