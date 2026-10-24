<?php
namespace App\Services;
use App\Models\Product;use Illuminate\Support\Collection;
class CartService{
 public function contents():array{return session('cart',[]);}
 public function add(int $id,int $qty):void{$p=Product::findOrFail($id);abort_unless($p->is_active&&$p->stock>0,422);$cart=$this->contents();$cart[$id]=min($p->stock,20,($cart[$id]??0)+$qty);session(['cart'=>$cart]);}
 public function remove(int $id):void{$c=$this->contents();unset($c[$id]);session(['cart'=>$c]);}
 public function items():Collection{$c=$this->contents();return Product::whereIn('id',array_keys($c))->where('is_active',true)->get()->each(fn($p)=>$p->cart_quantity=$c[$p->id]??0);}
 public function total():int{return (int)$this->items()->sum(fn($p)=>$p->price*$p->cart_quantity);}
 public function clear():void{session()->forget('cart');}
}
