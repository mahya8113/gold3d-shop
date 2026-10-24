<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{GoldMarketController, CatalogApiController, HealthController};

Route::get('/health', HealthController::class)->name('api.health');
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/gold-market', GoldMarketController::class)->name('api.gold-market');
    Route::get('/catalog', CatalogApiController::class)->name('api.catalog');
});
