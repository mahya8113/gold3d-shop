<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('products', function(Blueprint $t){ $t->decimal('gold_weight_grams',10,3)->nullable()->after('price'); $t->unsignedTinyInteger('gold_karat')->default(18)->after('gold_weight_grams'); $t->decimal('stone_cost',15,2)->default(0)->after('gold_karat'); $t->decimal('labor_percent_override',6,2)->nullable()->after('stone_cost'); });
  Schema::create('gold_price_events', function(Blueprint $t){ $t->id(); $t->decimal('old_price',15,2)->nullable(); $t->decimal('new_price',15,2); $t->decimal('change_percent',8,3)->default(0); $t->string('source')->default('tgju-live'); $t->timestamp('captured_at')->useCurrent(); $t->timestamps(); $t->index('captured_at'); });
 }
 public function down(): void { Schema::dropIfExists('gold_price_events'); Schema::table('products', function(Blueprint $t){$t->dropColumn(['gold_weight_grams','gold_karat','stone_cost','labor_percent_override']);}); }
};
