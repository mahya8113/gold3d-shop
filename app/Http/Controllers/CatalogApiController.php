<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'integer', 'min:1'],
            'min' => ['nullable', 'integer', 'min:0'],
            'max' => ['nullable', 'integer', 'min:0'],
            'available' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:featured,price_asc,price_desc,newest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $cacheKey = 'catalog:' . sha1(json_encode($validated, JSON_UNESCAPED_UNICODE));

        $payload = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($validated) {
            $query = Product::query()
                ->with('category:id,name,slug')
                ->where('is_active', true);

            if (!empty($validated['q'])) {
                $term = trim($validated['q']);
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            }

            if (!empty($validated['category'])) {
                $query->where('category_id', $validated['category']);
            }
            if (isset($validated['min'])) $query->where('price', '>=', $validated['min']);
            if (isset($validated['max'])) $query->where('price', '<=', $validated['max']);
            if (!empty($validated['available'])) $query->where('stock', '>', 0);

            match ($validated['sort'] ?? 'featured') {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'newest' => $query->latest(),
                default => $query->orderByDesc('stock')->latest(),
            };

            $page = $query->paginate($validated['per_page'] ?? 12, ['id', 'category_id', 'name', 'slug', 'price', 'stock', 'image_path', 'model_path'], 'page', $validated['page'] ?? 1);

            return [
                'ok' => true,
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'total' => $page->total(),
                ],
                'items' => $page->items(),
            ];
        });

        return response()->json($payload);
    }
}
