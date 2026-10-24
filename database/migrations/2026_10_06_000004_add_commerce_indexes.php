<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->index(['is_active', 'stock'], 'products_active_stock_idx');
            $t->index(['category_id', 'is_active'], 'products_category_active_idx');
            $t->index('price', 'products_price_idx');
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->index(['status', 'created_at'], 'orders_status_created_idx');
        });

        Schema::table('gold_rate_snapshots', function (Blueprint $t) {
            $t->index('captured_at', 'gold_snapshots_captured_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->dropIndex('products_active_stock_idx');
            $t->dropIndex('products_category_active_idx');
            $t->dropIndex('products_price_idx');
        });
        Schema::table('orders', fn (Blueprint $t) => $t->dropIndex('orders_status_created_idx'));
        Schema::table('gold_rate_snapshots', fn (Blueprint $t) => $t->dropIndex('gold_snapshots_captured_idx'));
    }
};
