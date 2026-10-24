@extends('layouts.app')
@section('title','Gold3D | ویترین سه‌بعدی طلا و جواهر')
@section('content')
<div class="luxury-strip"><span>● بازار طلا</span><span>قیمت ۱۸ عیار با منبع TGJU</span><span>پیش‌نمایش واقعی + 3D/GLB</span><span>پرداخت با زرین‌پال</span></div>

<section class="hero-ultra hero-obsidian">
  <div class="hero-ultra-copy">
    <div class="eyebrow"><span></span> GOLD3D · OBSIDIAN JEWELRY LAB</div>
    <h1>جواهرات را<br><em>قبل از خرید، زندگی کن.</em></h1>
    <p>ویترین Gold3D با قیمت لحظه‌ای طلای ۱۸ عیار، عکس واقعی محصول، مدل 3D/360 در صورت وجود GLB و مسیر پرداخت زرین‌پال.</p>
    <div class="hero-actions"><a class="gold-btn big" href="{{ route('products') }}">کشف مجموعه <b>←</b></a><a class="outline-btn" href="{{ route('features.hub') }}">استودیو سه‌بعدی</a></div>
    <div class="hero-proof"><div><b>12+</b><span>نمونه طراحی</span></div><div><b>18K</b><span>نرخ لحظه‌ای</span></div><div><b>3D</b><span>آماده GLB</span></div><div><b>ZP</b><span>زرین‌پال</span></div></div>
  </div>
  <div class="hero-ultra-stage hero-video-stage">
    <div class="hero-3d-glow" aria-hidden="true"></div>
    <video class="hero-video" autoplay muted loop playsinline preload="auto" aria-label="ویدیوی معرفی جواهرات Gold3D">
      <source src="{{ asset('videos/hero.mp4') }}" type="video/mp4">
    </video>
    <div class="hero-3d-label"><span>GOLD3D · SIGNATURE</span><strong>Gold3D Showcase</strong></div>
  </div>
</section>

<section class="gold-market" data-gold-market>
  <div class="market-head">
    <div><span class="eyebrow dark">LIVE MARKET · IRAN</span><h2>نبض طلای امروز</h2><p>قیمت پایه از بازار داخلی طلا دریافت می‌شود و هر دقیقه دوباره بررسی می‌گردد.</p></div>
    <div class="market-status" data-status>● در حال اتصال به بازار...</div>
  </div>
  <div class="market-grid">
    <article class="market-main-card">
      <div class="market-card-top"><span>طلای ۱۸ عیار / گرم</span><span>تومان</span></div>
      <div class="market-price" data-gold18>{{ number_format($market['gold18_toman']) }} تومان</div>
      <div class="market-change"><span>تغییر روزانه</span><b data-change>{{ number_format($market['change_percent'],2) }}%</b></div>
      <div class="market-meta">آخرین بررسی: <span data-updated>{{ $market['updated_at'] ? \Carbon\Carbon::parse($market['updated_at'])->format('Y/m/d H:i') : '—' }}</span></div>
    </article>
    <article class="market-mini"><span>طلای ۲۴ عیار</span><strong data-gold24>{{ $market['gold24_toman'] ? number_format($market['gold24_toman']).' تومان' : '—' }}</strong><small>هر گرم</small></article>
    <article class="market-mini"><span>اونس جهانی</span><strong data-ounce>{{ $market['ounce_usd'] ? '$'.number_format($market['ounce_usd'],2) : '—' }}</strong><small>USD / oz</small></article>
    <article class="market-chart-card">
      <div class="chart-head"><span>روند ذخیره‌شده Gold3D</span><strong data-gold-chart-latest>{{ $market['history'] ? number_format(end($market['history'])['price']).' تومان' : '—' }}</strong></div>
      <svg id="gold-live-chart" viewBox="0 0 900 300" preserveAspectRatio="none" aria-label="نمودار قیمت طلای ۱۸ عیار"></svg>
      <small>نمودار از لحظه اتصال Gold3D snapshot می‌گیرد؛ برای تاریخچه رسمی به منبع بازار مراجعه کنید.</small>
    </article>
  </div>
  <div class="market-source">منبع قیمت: <a href="https://www.tgju.org/profile/geram18" target="_blank" rel="noopener">TGJU / Gram Gold 18</a> · برای فروش واقعی، قیمت و واحد پول قبل از ثبت سفارش دوباره اعتبارسنجی می‌شود.</div>
</section>

<section id="collections" class="collections-section">
 <div class="section-title"><div><span class="eyebrow dark">CURATED CATEGORIES</span><h2>هر سبک، یک امضای متفاوت</h2></div><a href="{{ route('products') }}">همه محصولات ↗</a></div>
 <div class="category-tiles">
  @foreach($categories as $c)
   @php($categoryImages = ['rings'=>'https://images.pexels.com/photos/15967464/pexels-photo-15967464.jpeg?auto=compress&cs=tinysrgb&w=800','necklaces'=>'https://images.pexels.com/photos/10293692/pexels-photo-10293692.jpeg?auto=compress&cs=tinysrgb&w=800','bracelets'=>'https://images.pexels.com/photos/12124638/pexels-photo-12124638.jpeg?auto=compress&cs=tinysrgb&w=800','earrings'=>'https://images.pexels.com/photos/32797480/pexels-photo-32797480.jpeg?auto=compress&cs=tinysrgb&w=800'])
   <a class="category-tile" href="{{ route('products',['category'=>$c->id]) }}"><img src="{{ $categoryImages[$c->slug] ?? asset('images/product-ring-royal.svg') }}" alt="{{ $c->name }}"><div><span>COLLECTION</span><h3>{{ $c->name }}</h3><small>کشف مجموعه ←</small></div></a>
  @endforeach
 </div>
</section>

<section class="catalog-preview">
 <div class="section-title"><div><span class="eyebrow dark">THE SIGNATURE EDIT</span><h2>۱۲ نمونه منتخب</h2></div><p>هر کارت آماده ورود به جزئیات و نمایش 3D است.</p></div>
 <div class="product-grid premium-grid">
 @foreach($products->take(12) as $p)
  <article class="product-card premium-card obsidian-card">
   <div class="product-media"><a href="{{ route('products.show',$p) }}"><span class="pill">18K · GOLD3D</span><img src="{{ preg_match('/^https?:\/\//', (string) $p->image_path) ? $p->image_path : asset($p->image_path ?: 'images/product-ring-royal.svg') }}" alt="{{ $p->name }}"><span class="view-3d">◉ ورود به ویترین 3D</span></a>@auth<form method="post" action="{{ route('features.wishlist',$p) }}" class="heart-form">@csrf<button type="submit" class="heart" aria-label="افزودن یا حذف از علاقه‌مندی">♡</button></form>@else<a class="heart" href="{{ route('login') }}" aria-label="ورود برای علاقه‌مندی">♡</a>@endauth</div>
   <div class="product-info"><div><small>{{ optional($p->category)->name ?? 'Gold3D' }}</small><h3><a href="{{ route('products.show',$p) }}">{{ $p->name }}</a></h3><div class="rating">★★★★★ <span>18K · {{ $p->model_path ? '360°' : 'عکس واقعی' }}</span></div></div><strong>{{ number_format($p->price) }} <small>تومان</small></strong></div>
  </article>
 @endforeach
 </div>
</section>

<section class="architecture-section">
 <div class="section-title"><div><span class="eyebrow dark">GOLD3D ENGINE · APEX</span><h2>پشت این ویترین، یک موتور واقعی فروش است.</h2></div><span class="architecture-badge">API · CACHE · SECURITY · 3D</span></div>
 <div class="architecture-grid">
  <article class="architecture-card"><span>01</span><b>Live Market Engine</b><p>نرخ ۱۸ و ۲۴ عیار و اونس از سرویس بازار خوانده، snapshot می‌شود و با cache فشار روی provider کنترل می‌گردد.</p><i>LIVE / CACHE</i></article>
  <article class="architecture-card"><span>02</span><b>Commerce Core</b><p>سبد خرید، سفارش، موجودی، پرداخت، پروفایل و وضعیت سفارش روی Laravel و تراکنش‌های دیتابیس اجرا می‌شوند.</p><i>TRANSACTIONAL</i></article>
  <article class="architecture-card"><span>03</span><b>3D Rendering Lab</b><p>Three.js با کنترل 360 درجه، نور فیزیکی، ACES tone mapping و bloom برای ویترین تعاملی استفاده می‌شود.</p><i>WEBGL / HDR</i></article>
  <article class="architecture-card"><span>04</span><b>Secure Edge</b><p>هدرهای امنیتی، validation، rate limit روی APIهای عمومی و health endpoint برای پایش سرویس در نظر گرفته شده‌اند.</p><i>SECURE / OBSERVABLE</i></article>
 </div>
</section>

<section class="atelier-panel atelier-obsidian">
  <div class="atelier-copy">
    <span class="eyebrow">GOLD3D ATELIER</span>
    <h2>ویترین را به آزمایشگاه طراحی تبدیل کن.</h2>
    <p>رنگ طلا، عیار، وزن، سنگ و اندازه را تغییر بده؛ برآورد قیمت با نرخ بازار را ببین و طرح را ذخیره کن.</p>
    <a class="gold-btn" href="{{ route('features.hub') }}">ورود به استودیو 3D</a>
  </div>
  <div class="atelier-ring atelier-real-photo">
    <img src="https://images.unsplash.com/photo-1662833833862-4f5328756afa?auto=format&fit=crop&fm=jpg&q=88&w=1200" alt="انگشتر طلای واقعی در Gold3D" loading="lazy" decoding="async" referrerpolicy="no-referrer">
    <div class="atelier-real-overlay"></div>
    <span class="atelier-real-label">REAL JEWELRY</span>
  </div>
</section>
@endsection
