<?php
namespace App\Services;
use App\Models\{Product,ProductInteraction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class JewelryRecommender {
 private array $weights=['view'=>1,'compare'=>4,'wishlist'=>7,'cart'=>10,'purchase'=>16,'review'=>6];
 public function track(Request $request, Product $product, string $event): void {
  abort_unless(array_key_exists($event,$this->weights),404);
  ProductInteraction::create(['user_id'=>$request->user()?->id,'session_key'=>$request->session()->getId(),'product_id'=>$product->id,'event'=>$event,'weight'=>$this->weights[$event],'meta'=>['url'=>$request->path()]]);
  Cache::forget($this->audienceKey($request));
 }
 public function recommend(Request $request, ?Product $seed=null, int $limit=8): array {
  $key=$this->audienceKey($request).':'.($seed?->id??'home');
  return Cache::remember($key,now()->addMinutes(10),function()use($request,$seed,$limit){
   $products=Product::with('category:id,name,slug')->where('is_active',true)->where('stock','>',0)->get();
   if($products->isEmpty()) return [];
   $history=ProductInteraction::where(function($q)use($request){$q->where('session_key',$request->session()->getId());if($request->user())$q->orWhere('user_id',$request->user()->id);})->latest()->limit(80)->get();
   $liked=$history->groupBy('product_id')->map(fn($events)=>$events->sum('weight'));
   $categoryAffinity=[];$karatAffinity=[];$priceValues=[];
   foreach($history as $h){$p=$products->firstWhere('id',$h->product_id) ?? Product::find($h->product_id);if(!$p)continue;$categoryAffinity[$p->category_id]=($categoryAffinity[$p->category_id]??0)+$h->weight;$karatAffinity[(string)$p->gold_karat]=($karatAffinity[(string)$p->gold_karat]??0)+$h->weight;$priceValues[]=(float)$p->price;}
   $avgPrice=count($priceValues)?array_sum($priceValues)/count($priceValues):($seed?->price??0);
   $maxAffinity=max($categoryAffinity?:[1]); $maxLiked=max($liked->values()->all()?:[1]);
   return $products->map(function($p)use($seed,$liked,$categoryAffinity,$karatAffinity,$avgPrice,$maxAffinity,$maxLiked){
    $score=0;$reasons=[];
    if($seed){ if($p->category_id===$seed->category_id){$score+=35;$reasons[]='هم‌دسته با محصول انتخابی';} if((int)$p->gold_karat===(int)$seed->gold_karat){$score+=12;$reasons[]='هم‌عیار';} if($seed->price>0){$distance=abs($p->price-$seed->price)/$seed->price;$score+=max(0,20-(20*min(1,$distance)));if($distance<.18)$reasons[]='بازه قیمت نزدیک';}}
    $aff=($categoryAffinity[$p->category_id]??0)/$maxAffinity;$score+=25*$aff;if($aff>.55)$reasons[]='متناسب با علایق شما';
    $kar=(string)$p->gold_karat;$ka=($karatAffinity[$kar]??0)/max(1,max($karatAffinity?:[1]));$score+=10*$ka;if($ka>.5)$reasons[]='عیار مورد علاقه شما';
    if($avgPrice>0){$pd=abs($p->price-$avgPrice)/$avgPrice;$score+=max(0,10-(10*min(1,$pd)));}
    $score+=min(8,$p->stock/2); $score+=min(7,($p->reviews()->where('is_approved',true)->avg('rating')??0)*1.4);
    if(isset($liked[$p->id]))$score-=min(18,$liked[$p->id]/$maxLiked*18);
    if(!$reasons)$reasons[]='انتخاب هوشمند بر اساس محبوبیت و موجودی';
    return ['product'=>$p,'score'=>round($score,3),'reasons'=>array_values(array_unique($reasons))];
   })->sortByDesc('score')->take($limit)->values()->all();
  });
 }
 private function audienceKey(Request $r): string { return 'reco:'.($r->user()?->id?'u'.$r->user()->id:'s'.hash('sha256',$r->session()->getId())); }
}
