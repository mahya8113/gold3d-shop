<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('orders', function(Blueprint $t){
   $t->unsignedBigInteger('subtotal_amount')->default(0)->after('total_amount');
   $t->unsignedBigInteger('discount_amount')->default(0)->after('subtotal_amount');
   $t->unsignedBigInteger('shipping_amount')->default(0)->after('discount_amount');
   $t->unsignedBigInteger('tax_amount')->default(0)->after('shipping_amount');
   $t->string('shipping_method')->nullable()->after('tax_amount');
   $t->string('coupon_code')->nullable()->index()->after('shipping_method');
   $t->string('invoice_number')->nullable()->unique()->after('tracking_code');
   $t->timestamp('paid_at')->nullable()->after('invoice_number');
  });
  Schema::create('coupons', function(Blueprint $t){
   $t->id(); $t->string('code')->unique(); $t->string('type')->default('fixed');
   $t->unsignedBigInteger('value'); $t->unsignedBigInteger('min_order')->default(0);
   $t->unsignedInteger('usage_limit')->nullable(); $t->unsignedInteger('used_count')->default(0);
   $t->timestamp('starts_at')->nullable(); $t->timestamp('expires_at')->nullable();
   $t->boolean('is_active')->default(true); $t->timestamps();
  });
  Schema::create('order_addresses', function(Blueprint $t){
   $t->id(); $t->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
   $t->string('recipient_name'); $t->string('phone'); $t->string('province')->nullable();
   $t->string('city')->nullable(); $t->text('address'); $t->string('postal_code')->nullable();
   $t->timestamps();
  });
 } 
 public function down(): void {
  Schema::dropIfExists('order_addresses'); Schema::dropIfExists('coupons');
  Schema::table('orders', function(Blueprint $t){$t->dropColumn(['subtotal_amount','discount_amount','shipping_amount','tax_amount','shipping_method','coupon_code','invoice_number','paid_at']);});
 }
};
