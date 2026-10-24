<div class="apex-admin-shell">
  <aside class="apex-admin-sidebar">
    <div class="apex-admin-brand"><span>◇</span><div><b>Gold3D</b><small>APEX COMMERCE OS</small></div></div>
    <div class="apex-admin-section">مرکز کنترل</div>
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard')?'is-active':'' }}">◈ <span>داشبورد</span></a>
    <a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products*')?'is-active':'' }}">◇ <span>کاتالوگ</span></a>
    <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders*')?'is-active':'' }}">▣ <span>سفارش‌ها</span></a>
    <a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers')?'is-active':'' }}">◎ <span>مشتریان</span></a>
    <div class="apex-admin-section">عملیات</div>
    <a href="{{ route('admin.reviews') }}" class="{{ request()->routeIs('admin.reviews*')?'is-active':'' }}">✦ <span>نظرات</span></a>
    <a href="{{ route('admin.operations') }}" class="{{ request()->routeIs('admin.operations')?'is-active':'' }}">◫ <span>پشتیبانی و سفارش سفارشی</span></a>
    <a href="{{ route('admin.features') }}" class="{{ request()->routeIs('admin.features*')?'is-active':'' }}">◉ <span>قیمت طلا و ابزارها</span></a>
    <a href="{{ route('admin.audit') }}" class="{{ request()->routeIs('admin.audit')?'is-active':'' }}">⌁ <span>Audit Log</span></a>
    <div class="apex-admin-bottom"><a href="{{ route('home') }}">← بازگشت به فروشگاه</a></div>
  </aside>
  <section class="apex-admin-content">
    @if(session('success'))<div class="apex-alert success">✓ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="apex-alert danger">{{ $errors->first() }}</div>@endif
    {{ $slot ?? '' }}
    @yield('admin-content')
  </section>
</div>
