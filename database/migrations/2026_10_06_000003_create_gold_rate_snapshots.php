<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gold_rate_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_per_gram_toman');
            $table->string('source')->default('tgju-live');
            $table->timestamp('captured_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gold_rate_snapshots');
    }
};
