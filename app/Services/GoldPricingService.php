<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class GoldPricingService {
 public function current(): ?object { return DB::table('gold_rates')->latest('effective_at')->first(); }
 public function quote(float $weight,int $karat=18,float $stoneCost=0): int {
  $r=$this->current(); if(!$r) throw new \RuntimeException('ابتدا نرخ طلا در پنل مدیریت ثبت شود.');
  $base=(float)$r->price_per_gram*$weight*($karat/18);
  return (int) round(($base*(1+(float)$r->labor_percent/100)*(1+(float)$r->profit_percent/100)+$stoneCost)*(1+(float)$r->tax_percent/100));
 }
}
