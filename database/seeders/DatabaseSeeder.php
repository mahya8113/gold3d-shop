<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Category,Product};
class DatabaseSeeder extends Seeder{
 public function run():void{
  $cats=[];
  foreach([['rings','انگشتر'],['necklaces','گردنبند'],['bracelets','دستبند'],['earrings','گوشواره'],['pendants','آویز'],['chains','زنجیر'],['sets','ست جواهرات']] as [$slug,$name]) $cats[$slug]=Category::firstOrCreate(['slug'=>$slug],['name'=>$name]);
  $products=[
   ['gold-classic-ring','انگشتر Royal Halo','انگشتر طلای ۱۸ عیار با هاله نگین مرکزی و فرم کلاسیک.',12500000,8,'rings','https://images.pexels.com/photos/15967464/pexels-photo-15967464.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['signature-signet','انگشتر Signature','انگشتر مینیمال با صفحه صیقلی و شخصیت لوکس.',14900000,6,'rings','https://images.unsplash.com/photo-1627293501081-a1f856f447b4?auto=format&fit=crop&w=1200&q=85'],
   ['solitaire-ring','انگشتر Solitaire','طراحی تک‌نگین برای استایل رسمی و ماندگار.',17600000,5,'rings','https://images.pexels.com/photos/15967464/pexels-photo-15967464.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['constellation-necklace','گردنبند Constellation','گردنبند ظریف با فرم ستاره‌ای و آویز مرکزی.',18900000,5,'necklaces','https://images.pexels.com/photos/10293692/pexels-photo-10293692.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['teardrop-necklace','گردنبند Teardrop','آویز اشکی ظریف برای استایل روزمره و مجلسی.',16400000,7,'necklaces','https://images.pexels.com/photos/12145316/pexels-photo-12145316.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['tennis-bracelet','دستبند Tennis','دستبند ردیفی با جلوه درخشان و وزن متعادل.',26500000,4,'bracelets','https://images.pexels.com/photos/12124638/pexels-photo-12124638.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['luxe-cuff','دستبند Luxe Cuff','دستبند باز با طراحی معماری و پرداخت آینه‌ای.',22100000,5,'bracelets','https://images.pexels.com/photos/12124662/pexels-photo-12124662.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['pearl-drop-earrings','گوشواره Pearl Drop','گوشواره آویز با فرم نرم و نگین درخشان.',9800000,10,'earrings','https://images.pexels.com/photos/32797480/pexels-photo-32797480.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['halo-hoop-earrings','گوشواره Halo Hoop','گوشواره حلقه‌ای مدرن با جزئیات نگین‌دار.',11200000,9,'earrings','https://images.pexels.com/photos/15785483/pexels-photo-15785483.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['moonlight-pendant','آویز Moonlight','آویز هندسی الهام‌گرفته از ماه با زنجیر ظریف.',8700000,12,'pendants','https://images.pexels.com/photos/39239/pexels-photo-39239.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['luxe-chain','زنجیر Luxe','زنجیر طلای ظریف با بافت لوکس و انعطاف‌پذیر.',19800000,6,'chains','https://images.pexels.com/photos/12145316/pexels-photo-12145316.jpeg?auto=compress&cs=tinysrgb&w=1200'],
   ['royal-signature-set','ست Royal Signature','ست هماهنگ انگشتر و گردنبند برای استایل کامل.',38900000,3,'sets','https://images.pexels.com/photos/24815712/pexels-photo-24815712.jpeg?auto=compress&cs=tinysrgb&w=1200'],
  ];
  foreach($products as $row){ [$slug,$name,$desc,$price,$stock,$cat,$image]=$row; $weights=['gold-classic-ring'=>3.1,'signature-signet'=>4.0,'solitaire-ring'=>3.6,'constellation-necklace'=>4.8,'teardrop-necklace'=>4.2,'tennis-bracelet'=>6.5,'luxe-cuff'=>7.1,'pearl-drop-earrings'=>2.8,'halo-hoop-earrings'=>3.0,'moonlight-pendant'=>2.1,'luxe-chain'=>5.4,'royal-signature-set'=>9.2]; $models=[
   'gold-classic-ring'=>'models3d/ring-classic.glb',
   'solitaire-ring'=>'models3d/ring-classic.glb',
   'signature-signet'=>'models3d/ring-signature.glb',
   'royal-signature-set'=>'models3d/ring-signature.glb',
  ];
  Product::updateOrCreate(['slug'=>$slug],['category_id'=>$cats[$cat]->id,'name'=>$name,'description'=>$desc,'price'=>$price,'gold_weight_grams'=>$weights[$slug]??null,'gold_karat'=>18,'stone_cost'=>0,'stock'=>$stock,'model_path'=>$models[$slug]??null,'image_path'=>$image,'is_active'=>true]); }
  if (!\Illuminate\Support\Facades\DB::table('gold_rates')->exists()) {\Illuminate\Support\Facades\DB::table('gold_rates')->insert(['price_per_gram'=>8500000,'labor_percent'=>12,'profit_percent'=>8,'tax_percent'=>10,'source'=>'seed-fallback','effective_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);}
 }
}
