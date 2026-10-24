<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('product_interactions', function(Blueprint $t){
   $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('session_key')->index();
   $t->foreignId('product_id')->constrained()->cascadeOnDelete(); $t->string('event',32); $t->unsignedInteger('weight')->default(1); $t->json('meta')->nullable(); $t->timestamps();
   $t->index(['session_key','event']); $t->index(['user_id','event']); $t->index(['product_id','event']);
  });
  Schema::create('recommendation_cache', function(Blueprint $t){
   $t->id(); $t->string('audience_key')->index(); $t->foreignId('product_id')->constrained()->cascadeOnDelete(); $t->decimal('score',12,6); $t->json('reasons')->nullable(); $t->timestamp('expires_at')->index(); $t->timestamps();
   $t->index(['audience_key','score']); $t->unique(['audience_key','product_id']);
  });
 }
 public function down(): void { Schema::dropIfExists('recommendation_cache'); Schema::dropIfExists('product_interactions'); }
};
