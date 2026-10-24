@extends('layouts.app')
@section('title','Gold3D Apex | Control Center')
@section('content')
@include('admin.partials.nav')
<div class="apex-page-head"><div><div class="apex-kicker">APEX COMMERCE OS / LIVE CONTROL</div><h1>مرکز فرماندهی Gold3D</h1><p>نمای زنده از فروش، عملیات، موجودی و سلامت کاتالوگ.</p></div><div class="apex-live-pill"><i></i> SYSTEM ONLINE <span>{{ now()->format('Y/m/d · H:i') }}</span></div></div>
<div class="apex-kpis">
  <div class="apex-kpi"><span>فروش کل</span><b>{{ number_format($sales) }}</b><small>تومان</small></div>
  <div class="apex-kpi"><span>فروش ۳۰ روز</span><b>{{ number_format($paidMonth) }}</b><small>تومان</small></div>
  <div class="apex-kpi"><span>سفارش امروز</span><b>{{ $todayOrders }}</b><small>سفارش</small></div>
  <div class="apex-kpi"><span>مشتریان</span><b>{{ number_format($customers) }}</b><small>حساب فعال</small></div>
  <div class="apex-kpi"><span>کاتالوگ فعال</span><b>{{ $activeProducts }}</b><small>از {{ $products }} محصول</small></div>
  <div class="apex-kpi {{ $lowStock ? 'warning':'' }}"><span>موجودی کم</span><b>{{ $lowStock }}</b><small>نیازمند بررسی</small></div>
</div>
<div class="apex-dashboard-grid">
  <section class="apex-panel apex-chart-panel"><div class="apex-panel-head"><div><span>SALES VELOCITY</span><h2>ریتم فروش · ۷ روز اخیر</h2></div><strong>{{ number_format($paidWeek) }} تومان</strong></div><div class="apex-bars">@php($max=max(1,$daily->max('sales')) )@foreach($daily as $day)<div class="apex-bar-col"><div class="apex-bar" style="height:{{ max(7,($day['sales']/$max)*100) }}%"><em>{{ number_format($day['sales']/1000000,1) }}M</em></div><small>{{ $day['label'] }}</small></div>@endforeach</div></section>
  <section class="apex-panel"><div class="apex-panel-head"><div><span>ORDER PIPELINE</span><h2>قیف سفارش</h2></div></div><div class="apex-status-list">@foreach(['pending'=>'در انتظار','paid'=>'پرداخت‌شده','processing'=>'در پردازش','shipped'=>'ارسال‌شده','completed'=>'تکمیل‌شده','cancelled'=>'لغوشده'] as $key=>$label)<div><span><i class="dot {{ $key }}"></i>{{ $label }}</span><b>{{ $status[$key] ?? 0 }}</b></div>@endforeach</div></section>
</div>
<div class="apex-dashboard-grid lower">
<section class="apex-panel"><div class="apex-panel-head"><div><span>TOP PERFORMERS</span><h2>محصولات پرفروش</h2></div><a href="{{ route('admin.products') }}">مشاهده کاتالوگ ←</a></div><div class="apex-table"><div class="tr th"><span>محصول</span><span>تعداد</span><span>درآمد</span></div>@forelse($topProducts as $p)<div class="tr"><span>{{ $p->product_name }}</span><b>{{ $p->units }}</b><b>{{ number_format($p->revenue) }} تومان</b></div>@empty<div class="apex-empty">هنوز داده فروش ثبت نشده است.</div>@endforelse</div></section>
<section class="apex-panel"><div class="apex-panel-head"><div><span>ACTION CENTER</span><h2>نیازمند اقدام</h2></div></div><div class="action-cards"><a href="{{ route('admin.reviews') }}"><b>{{ $reviewsPending }}</b><span>نظر در انتظار تأیید</span>→</a><a href="{{ route('admin.operations') }}"><b>{{ $ticketsOpen }}</b><span>تیکت باز</span>→</a><a href="{{ route('admin.operations') }}"><b>{{ $customOrders }}</b><span>سفارش سفارشی</span>→</a></div></section>
</div>
@endsection
