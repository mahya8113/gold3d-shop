<?php

namespace App\Http\Controllers;

use App\Services\GoldMarketService;
use Illuminate\Http\JsonResponse;

class GoldMarketController extends Controller
{
    public function __invoke(GoldMarketService $market): JsonResponse
    {
        return response()->json($market->dashboard());
    }
}
