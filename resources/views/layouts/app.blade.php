<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d0b09">
    <link rel="manifest" href="/manifest.webmanifest">
    <title>@yield('title','Gold3D | جواهرات لوکس')</title>
    @vite(['resources/css/app.css','resources/css/features.css','resources/css/luxury-apex.css','resources/js/app.js','resources/js/studio.js','resources/js/pwa.js','resources/js/gold-market.js','resources/js/luxury-apex.js'])
</head>
<body>
<div class="site-noise"></div>
<header class="site-header deluxe-header">
    <div class="header-inner deluxe-inner">
        <a class="brand deluxe-brand" href="{{ route('home') }}"><span class="brand-mark">◇</span><span><b>Gold</b>3D<small>REAL GOLD · REAL BEAUTY</small></span></a>
        <form class="header-search" action="{{ route('products') }}" method="get"><input name="q" placeholder="جستجوی محصول، دسته یا کد..." value="{{ request('q') }}"><button aria-label="جستجو">⌕</button></form>
        <nav class="main-nav deluxe-nav"><a class="active" href="{{ route('home') }}">صفحه اصلی</a><a href="{{ route('products') }}">محصولات</a><a href="{{ route('features.hub') }}">استودیو 3D ◇</a><a href="{{ route('features.tools') }}">خدمات</a></nav>
        <div class="header-actions deluxe-actions">@auth<a class="header-action" href="{{ route('features.wishlist.index') }}">♡<span>علاقه‌مندی</span></a>@else<a class="header-action" href="{{ route('login') }}">♡<span>علاقه‌مندی</span></a>@endauth<a class="header-action" href="{{ route('features.compare') }}">⇄<span>مقایسه</span></a><a class="header-action" href="{{ route('cart') }}">🛒<span>سبد خرید</span></a>@auth<a class="header-action" href="{{ route('profile') }}">♙<span>حساب کاربری</span></a>@else<a class="header-action" href="{{ route('login') }}">♙<span>ورود</span></a>@endauth</div>
    </div>
</header>
<main class="site-main">
    @if(session('success'))<div class="toast success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="toast error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('content')
</main>
<footer class="site-footer deluxe-footer">
  <div class="footer-grid deluxe-footer-grid"><div><div class="footer-brand">◇ Gold3D</div><p>جواهرات لوکس در یک تجربه دیجیتال؛ از مشاهده سه‌بعدی تا شخصی‌سازی و پرداخت آنلاین.</p><div class="footer-social">◎　◉　◌　◇</div></div><div><h4>دسترسی سریع</h4><a href="{{ route('home') }}">صفحه اصلی</a><a href="{{ route('products') }}">محصولات</a><a href="{{ route('features.hub') }}">استودیو 3D</a><a href="{{ route('features.finder') }}">مشاور خرید</a><a href="{{ route('features.tools') }}">خدمات</a><a href="{{ route('features.finder') }}">مشاور خرید هوشمند</a></div><div><h4>خدمات مشتریان</h4><span>راهنمای خرید</span><span>شرایط و قوانین</span><span>حریم خصوصی</span><span>گواهی اصالت</span></div><div class="footer-news"><h4>عضویت در خبرنامه</h4><p>برای دریافت جدیدترین محصولات و پیشنهادها ایمیل خود را وارد کنید.</p><form method="post" action="{{ route('newsletter.subscribe') }}"><input type="email" name="email" placeholder="ایمیل شما" required maxlength="190"><button type="submit">عضویت</button>@csrf</form></div></div><div class="footer-bottom">Gold3D © {{ date('Y') }} — Real Gold · Real Beauty</div>
</footer>
</body>
</html>