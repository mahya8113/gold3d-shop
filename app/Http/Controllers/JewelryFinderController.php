<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class JewelryFinderController extends Controller
{
    public function index()
    {
        return view('features.finder', [
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function recommend(Request $request)
    {
        $data = $request->validate([
            'budget' => ['required', 'integer', 'min:100000', 'max:5000000000'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'karat' => ['nullable', 'integer', 'in:18,24'],
            'style' => ['nullable', 'string', 'max:40'],
        ]);

        $budget = (int) $data['budget'];
        $query = Product::query()
            ->with('category:id,name,slug')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('price', '<=', $budget);

        if (!empty($data['category'])) {
            $query->where('category_id', $data['category']);
        }

        if (!empty($data['karat'])) {
            $query->where(function ($q) use ($data) {
                $q->where('gold_karat', $data['karat'])->orWhereNull('gold_karat');
            });
        }

        $style = trim((string) ($data['style'] ?? ''));
        $products = $query->get();

        $ranked = $products->map(function (Product $product) use ($budget, $style, $data) {
            $score = 0;
            $reasons = [];
            $price = (int) $product->price;
            $budgetFit = $budget > 0 ? max(0, 1 - abs($budget - $price) / $budget) : 0;
            $score += $budgetFit * 55;

            if ($price <= $budget) {
                $reasons[] = 'داخل بودجه شماست';
            }
            if ($budgetFit >= 0.75) {
                $reasons[] = 'از نظر بودجه انتخاب نزدیک‌تری است';
            }

            if (!empty($data['karat']) && (int) $product->gold_karat === (int) $data['karat']) {
                $score += 20;
                $reasons[] = $product->gold_karat . ' عیار مطابق انتخاب شما';
            }

            if ($product->model_path) {
                $score += 8;
                $reasons[] = 'نمایش سه‌بعدی دارد';
            }

            if ((int) $product->stock > 0) {
                $score += min(7, (int) $product->stock);
            }

            if ($style !== '') {
                $haystack = mb_strtolower(($product->name ?? '') . ' ' . ($product->description ?? ''));
                $needle = mb_strtolower($style);
                if (mb_stripos($haystack, $needle) !== false) {
                    $score += 18;
                    $reasons[] = 'با سبک انتخابی شما هم‌خوانی دارد';
                }
            }

            if (!$reasons) {
                $reasons[] = 'بهترین تطابق موجود بر اساس قیمت و موجودی';
            }

            return [
                'product' => $product,
                'score' => round($score, 1),
                'reasons' => array_values(array_unique($reasons)),
            ];
        })->sortByDesc('score')->take(6)->values();

        return response()->json([
            'count' => $ranked->count(),
            'results' => $ranked->map(fn ($item) => [
                'id' => $item['product']->id,
                'name' => $item['product']->name,
                'slug' => $item['product']->slug,
                'price' => (int) $item['product']->price,
                'image' => $item['product']->image_path,
                'category' => $item['product']->category?->name,
                'model3d' => (bool) $item['product']->model_path,
                'score' => $item['score'],
                'reasons' => $item['reasons'],
            ])->all(),
        ]);
    }
}
