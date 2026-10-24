@extends('layouts.app')
@section('title','Gold3D AI · پیشنهادهای هوشمند')
@section('content')
<section class="ai-hero">
 <div><span class="eyebrow">GOLD3D INTELLIGENCE</span><h1>انتخاب بعدی شما،<br><strong>هوشمندانه‌تر.</strong></h1><p>موتور پیشنهاد Gold3D از رفتار همین نشست، علاقه‌مندی‌ها، مقایسه‌ها، قیمت، عیار، دسته‌بندی، موجودی و امتیاز محصولات برای ساخت پیشنهاد شخصی استفاده می‌کند.</p></div>
 <div class="ai-orb"><span>AI</span><small>HYBRID<br>ENGINE</small></div>
</section>
<section class="recommend-grid">
 @forelse($recommendations as $item)
  @php($p=$item['product'])
  <article class="recommend-card">
   <a href="{{ route('products.show',$p->slug) }}" class="recommend-image"><img src="{{ preg_match('/^https?:\\/\\//',(string)$p->image_path)?$p->image_path:asset('images/products/'.$p->image_path) }}" alt="{{ $p->name }}"><span>{{ number_format($item['score'],1) }} AI</span></a>
   <div class="recommend-body"><small>{{ $p->category?->name }}</small><h3>{{ $p->name }}</h3><strong>{{ number_format($p->price) }} تومان</strong><div class="reason-list">@foreach($item['reasons'] as $reason)<i>✦ {{ $reason }}</i>@endforeach</div></div>
  </article>
 @empty
 <div class="empty-state">هنوز داده کافی برای شخصی‌سازی نداریم؛ چند محصول را ببینید یا به علاقه‌مندی‌ها اضافه کنید.</div>
 @endforelse
</section>
<script>
window.addEventListener('load',()=>{document.querySelectorAll('.recommend-card a').forEach(a=>a.addEventListener('click',()=>{}));});
</script>
@endsection
