@extends('layouts.app')
@section('title','Gold3D Apex | Customers')
@section('content')
@include('admin.partials.nav')
<div class="apex-page-head"><div><div class="apex-kicker">CUSTOMER INTELLIGENCE</div><h1>مشتریان</h1><p>ارزش خرید، تعداد سفارش و اطلاعات تماس را در یک نمای عملیاتی ببین.</p></div></div>
<form class="apex-toolbar" method="get"><input name="q" value="{{ request('q') }}" placeholder="نام، ایمیل یا موبایل..."><button>جستجو</button></form>
<div class="apex-customer-table"><div class="tr th"><span>مشتری</span><span>تماس</span><span>سفارش</span><span>ارزش خرید</span><span>عضویت</span></div>@forelse($customers as $c)@php($stats=$orderCounts[$c->id]??null)<div class="tr"><span><b>{{ $c->name }}</b><small>{{ $c->email }}</small></span><span>{{ $c->phone ?: '—' }}</span><b>{{ $stats->orders ?? 0 }}</b><b>{{ number_format($stats->paid_total ?? 0) }} تومان</b><span>{{ \Carbon\Carbon::parse($c->created_at)->format('Y/m/d') }}</span></div>@empty<div class="apex-empty">مشتری‌ای پیدا نشد.</div>@endforelse</div>{{ $customers->links() }}
@endsection
