@extends('layouts.app')
@section('title','Gold3D Apex | Reviews')
@section('content')
@include('admin.partials.nav')
<div class="apex-page-head"><div><div class="apex-kicker">TRUST & QUALITY</div><h1>مدیریت نظرات</h1><p>نظرات مشتریان را بررسی و فقط موارد تأییدشده را در ویترین منتشر کن.</p></div></div>
<div class="apex-review-grid">@forelse($reviews as $review)<article class="apex-review"><div class="review-stars">{{ str_repeat('★',$review->rating) }}<span>{{ str_repeat('☆',5-$review->rating) }}</span></div><h3>{{ $review->product?->name ?? 'محصول حذف‌شده' }}</h3><p>{{ $review->body ?: 'بدون متن' }}</p><small>{{ $review->user?->name ?? 'کاربر' }} · {{ $review->created_at?->format('Y/m/d') }}</small><form method="post" action="{{ route('admin.reviews.update',$review) }}">@csrf @method('PATCH')<input type="hidden" name="is_approved" value="{{ $review->is_approved ? 0 : 1 }}"><button class="{{ $review->is_approved?'apex-ghost':'apex-primary' }}">{{ $review->is_approved?'لغو انتشار':'تأیید و انتشار' }}</button></form></article>@empty<div class="apex-empty">نظری ثبت نشده است.</div>@endforelse</div>{{ $reviews->links() }}
@endsection
