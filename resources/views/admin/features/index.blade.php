@extends('layouts.app')
@section('content')
<h1>مرکز عملیات Gold3D</h1>
<div class="grid"><div class="card"><h3>هشدارهای فعال قیمت</h3><strong>{{ $alerts }}</strong></div><div class="card"><h3>آخرین نرخ طلا</h3><strong>{{ $rate ? number_format($rate->price_per_gram) : 'ثبت نشده' }}</strong></div><div class="card"><h3>درخواست‌های ساخت</h3><strong>{{ $customOrders->count() }}</strong></div><div class="card"><h3>تیکت‌های اخیر</h3><strong>{{ $tickets->count() }}</strong></div></div>
<section class="card"><h2>ثبت نرخ و هزینه‌های محاسباتی</h2><form method="post" action="{{ route('admin.features.rate') }}">@csrf<label>قیمت هر گرم طلای مبنا (تومان)<input type="number" name="price_per_gram" min="1" required></label><label>درصد اجرت<input type="number" name="labor_percent" min="0" max="300" step=".01" value="12"></label><label>درصد سود<input type="number" name="profit_percent" min="0" max="300" step=".01" value="8"></label><label>درصد مالیات<input type="number" name="tax_percent" min="0" max="100" step=".01" value="0"></label><button>ثبت نرخ جدید</button></form></section>
<section class="card"><h2>درخواست‌های سفارشی اخیر</h2>@foreach($customOrders as $item)<p>#{{ $item->id }} — {{ $item->title }} — {{ $item->status }} — {{ $item->created_at }}</p>@endforeach</section>
<section class="card"><h2>تیکت‌های پشتیبانی اخیر</h2>@foreach($tickets as $item)<p>#{{ $item->id }} — {{ $item->subject }} — {{ $item->status }}</p>@endforeach</section>
@endsection
