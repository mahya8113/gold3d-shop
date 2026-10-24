@extends('layouts.app')
@section('content')<section class="card"><h1>استعلام گواهی</h1><p>سریال: {{ $certificate->serial }}</p><p>تاریخ صدور: {{ $certificate->issued_at ?? 'در انتظار صدور' }}</p><p>جزئیات: {{ json_encode(json_decode($certificate->details),JSON_UNESCAPED_UNICODE) }}</p></section>@endsection
