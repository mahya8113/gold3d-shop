@extends('layouts.app')
@section('title','Gold3D Apex | Audit')
@section('content')
@include('admin.partials.nav')
<div class="apex-page-head"><div><div class="apex-kicker">SECURITY / TRACEABILITY</div><h1>Audit Log</h1><p>رویدادهای مدیریتی ثبت‌شده برای ردیابی عملیات حساس.</p></div></div>
<div class="apex-audit-table"><div class="tr th"><span>زمان</span><span>کاربر</span><span>عملیات</span><span>موضوع</span><span>IP Hash</span></div>@forelse($logs as $l)<div class="tr"><span>{{ \Carbon\Carbon::parse($l->created_at)->format('Y/m/d H:i:s') }}</span><span>{{ $l->user_id ?: 'system' }}</span><b>{{ $l->action }}</b><span>{{ $l->subject_type }} {{ $l->subject_id }}</span><code>{{ $l->ip_hash ?: '—' }}</code></div>@empty<div class="apex-empty">لاگ مدیریتی ثبت نشده است.</div>@endforelse</div>{{ $logs->links() }}
@endsection
