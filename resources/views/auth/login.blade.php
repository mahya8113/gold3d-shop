@extends('layouts.app')
@section('title','ورود | Gold3D')
@section('content')
<section class="auth-shell">
  <div class="auth-visual" aria-hidden="true">
    <span class="eyebrow">GOLD3D · MEMBERS</span>
    <h2>به ویترین اختصاصی خود خوش آمدید.</h2>
    <p>سفارش‌ها، علاقه‌مندی‌ها و طرح‌های ذخیره‌شده‌ی استودیو را در یک جا ببینید.</p>
    <ul><li>◇ پیگیری سفارش و پرداخت امن</li><li>◇ هشدار قیمت و علاقه‌مندی‌ها</li><li>◇ ذخیره طرح‌های استودیو 3D</li></ul>
  </div>
  <div class="auth-card">
    <div class="auth-head">
      <span class="auth-mark">◇</span>
      <h1>ورود به حساب کاربری</h1>
      <p>ایمیل و گذرواژه خود را وارد کنید.</p>
    </div>
    <form method="post" action="{{ route('login.store') }}" class="auth-form">
      @csrf
      <label class="auth-field"><span>ایمیل</span><input type="email" name="email" required autocomplete="email" placeholder="name@example.com" value="{{ old('email') }}"></label>
      <label class="auth-field"><span>گذرواژه</span><input type="password" name="password" required autocomplete="current-password" placeholder="••••••••"></label>
      <label class="auth-remember"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
      <button type="submit" class="gold-btn auth-submit">ورود ←</button>
    </form>
    <div class="auth-foot">حساب کاربری ندارید؟ <a href="{{ route('register') }}">ثبت‌نام کنید</a></div>
  </div>
</section>
@endsection
