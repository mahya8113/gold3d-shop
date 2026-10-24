<?php
namespace App\Services;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class GoldIntelligenceService {
 public function rate(): ?object { return DB::table('gold_rates')->latest('effective_at')->first(); }
 public function quote(Product $p): array {
  $rate=$this->rate(); if(!$rate || !$p->gold_weight_grams) return ['dynamic'=>false,'price'=>(int)$p->price,'base_gold'=>0,'labor'=>0,'profit'=>0,'tax'=>0,'gold_price'=>(int)($rate->price_per_gram??0),'weight'=>(float)($p->gold_weight_grams??0),'karat'=>(int)($p->gold_karat??18)];
  $base=(float)$rate->price_per_gram*(float)$p->gold_weight_grams*((int)$p->gold_karat/18);
  $laborPct=$p->labor_percent_override!==null?(float)$p->labor_percent_override:(float)$rate->labor_percent; $labor=$base*$laborPct/100;
  $profitPct=(float)$rate->profit_percent; $profit=($base+$labor+(float)$p->stone_cost)*$profitPct/100;
  $taxPct=(float)$rate->tax_percent; $tax=($base+$labor+$profit+(float)$p->stone_cost)*$taxPct/100;
  $price=(int)round($base+$labor+$profit+(float)$p->stone_cost+$tax);
  return ['dynamic'=>true,'price'=>$price,'base_gold'=>(int)round($base),'labor'=>(int)round($labor),'profit'=>(int)round($profit),'tax'=>(int)round($tax),'stone_cost'=>(int)$p->stone_cost,'gold_price'=>(int)$rate->price_per_gram,'weight'=>(float)$p->gold_weight_grams,'karat'=>(int)$p->gold_karat,'labor_percent'=>$laborPct,'profit_percent'=>$profitPct,'tax_percent'=>$taxPct];
 }
 public function refreshProductPrice(Product $p): int { $q=$this->quote($p); if(!$q['dynamic']) return (int)$p->price; $p->price=$q['price']; $p->saveQuietly(); return $q['price']; }
 public function marketSummary(): array { $r=$this->rate(); return ['price_18'=>(int)($r->price_per_gram??0),'labor'=>(float)($r->labor_percent??0),'profit'=>(float)($r->profit_percent??0),'tax'=>(float)($r->tax_percent??0),'effective_at'=>$r?->effective_at]; }
}
