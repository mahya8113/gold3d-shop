@extends('layouts.app')
@section('title','علاقه‌مندی‌ها | Gold3D')
@section('content')
<section class="customer-hero"><div><span class="eyebrow dark">PRIVATE COLLECTION</span><h1>علاقه‌مندی‌های من</h1><p>قطعاتی که برای بررسی دوباره ذخیره کرده‌اید.</p></div><a class="ghost-btn light" href="{{ route('products') }}">ادامه کشف جواهرات ←</a></section>
<div class="product-grid premium-grid customer-grid">
@forelse($products as $p)<article class="product-card premium-card"><div class="product-media"><a href="{{ route('products.show',$p) }}"><span class="pill">18K · SAVED</span><img src="{{ preg_match('/^https?:\/\//',(string)$p->image_path)?$p->image_path:asset($p->image_path ?: 'images/product-ring-royal.svg') }}" alt="{{ $p->name }}"></a></div><div class="product-info"><div><small>{{ optional($p->category)->name }}</small><h3><a href="{{ route('products.show',$p) }}">{{ $p->name }}</a></h3></div><strong>{{ number_format($p->price) }} <small>تومان</small></strong></div><div class="saved-actions"><form method="post" action="{{ route('features.wishlist',$p) }}">@csrf<button class="text-danger">حذف</button></form><form method="post" action="{{ route('features.compare.add',$p) }}">@csrf<button>مقایسه</button></form></div></article>@empty<div class="empty-state deluxe-empty"><b>هنوز چیزی ذخیره نکرده‌اید.</b><span>از گالری، قطعات مورد علاقه‌تان را اضافه کنید.</span><a class="gold-btn" href="{{ route('products') }}">مشاهده گالری</a></div>@endforelse
</div>
@endsection
