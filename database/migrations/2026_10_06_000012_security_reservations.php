<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('products', function(Blueprint $t){ $t->unsignedInteger('reserved_stock')->default(0)->after('stock'); });
  Schema::table('coupons', function(Blueprint $t){ $t->unsignedInteger('reserved_count')->default(0)->after('used_count'); });
  Schema::table('orders', function(Blueprint $t){ $t->foreignId('coupon_id')->nullable()->after('coupon_code')->constrained('coupons')->nullOnDelete(); $t->timestamp('reservation_expires_at')->nullable()->index()->after('paid_at'); });
 }
 public function down(): void {
  Schema::table('orders', function(Blueprint $t){ $t->dropForeign(['coupon_id']); $t->dropColumn(['coupon_id','reservation_expires_at']); });
  Schema::table('coupons', function(Blueprint $t){ $t->dropColumn('reserved_count'); });
  Schema::table('products', function(Blueprint $t){ $t->dropColumn('reserved_stock'); });
 }
};
