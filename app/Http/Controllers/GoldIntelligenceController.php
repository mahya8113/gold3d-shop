<?php
namespace App\Http\Controllers;
use App\Models\Product; use App\Services\GoldIntelligenceService; use Illuminate\Http\Request;
class GoldIntelligenceController extends Controller {
 public function quote(Product $product, GoldIntelligenceService $intel){ abort_unless($product->is_active,404); return response()->json($intel->quote($product)); }
 public function dashboard(GoldIntelligenceService $intel){ abort_unless(auth()->user()?->is_admin,403); return response()->json($intel->marketSummary()); }
}
