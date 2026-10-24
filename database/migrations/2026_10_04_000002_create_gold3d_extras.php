<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('gold_rates',function(Blueprint $t){$t->id();$t->decimal('price_per_gram',15,2);$t->decimal('labor_percent',6,2)->default(0);$t->decimal('profit_percent',6,2)->default(0);$t->decimal('tax_percent',6,2)->default(0);$t->string('source')->default('manual');$t->timestamp('effective_at')->useCurrent();$t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('wishlists',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->timestamps();$t->unique(['user_id','product_id']);});
  Schema::create('price_alerts',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->unsignedBigInteger('target_price');$t->boolean('is_active')->default(true);$t->timestamp('notified_at')->nullable();$t->timestamps();$t->unique(['user_id','product_id']);});
  Schema::create('product_comparisons',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('session_key')->index();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->timestamps();$t->unique(['session_key','product_id']);});
  Schema::create('custom_designs',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();$t->string('design_type')->default('ring');$t->json('configuration');$t->unsignedBigInteger('estimated_price')->default(0);$t->string('status')->default('draft');$t->timestamps();});
  Schema::create('certificates',function(Blueprint $t){$t->id();$t->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();$t->string('serial')->unique();$t->json('details');$t->timestamp('issued_at')->nullable();$t->timestamps();});
  Schema::create('gift_cards',function(Blueprint $t){$t->id();$t->string('code')->unique();$t->unsignedBigInteger('initial_amount');$t->unsignedBigInteger('balance');$t->timestamp('expires_at')->nullable();$t->foreignId('purchased_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('custom_orders',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('title');$t->text('brief');$t->json('reference_files')->nullable();$t->string('status')->default('submitted');$t->unsignedBigInteger('quoted_amount')->nullable();$t->timestamps();});
  Schema::create('audit_logs',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('action');$t->string('subject_type')->nullable();$t->string('subject_id')->nullable();$t->json('metadata')->nullable();$t->string('ip_hash',64)->nullable();$t->timestamps();});
  Schema::create('support_tickets',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('subject');$t->text('message');$t->string('status')->default('open');$t->timestamps();});
 }
 public function down(): void {foreach(['support_tickets','audit_logs','custom_orders','gift_cards','certificates','custom_designs','product_comparisons','price_alerts','wishlists','gold_rates'] as $table) Schema::dropIfExists($table);}
};
