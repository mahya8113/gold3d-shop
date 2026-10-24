@extends('layouts.app')
@section('title','مقایسه جواهرات | Gold3D')
@section('content')
<section class="customer-hero"><div><span class="eyebrow dark">COMPARE LAB</span><h1>مقایسه هوشمند جواهرات</h1><p>قیمت، موجودی، عیار و تجربه سه‌بعدی را کنار هم ببینید.</p></div><a class="ghost-btn light" href="{{ route('products') }}">افزودن قطعه دیگر +</a></section>
@if($products->isEmpty())<div class="empty-state deluxe-empty"><b>لیست مقایسه خالی است.</b><span>از صفحه هر محصول گزینه «مقایسه» را بزنید.</span><a class="gold-btn" href="{{ route('products') }}">رفتن به گالری</a></div>@else
<div class="compare-shell"><div class="compare-head"><span>ویژگی</span>@foreach($products as $p)<div><img src="{{ preg_match('/^https?:\/\//',(string)$p->image_path)?$p->image_path:asset($p->image_path ?: 'images/product-ring-royal.svg') }}" alt=""><b>{{ $p->name }}</b></div>@endforeach</div>
@foreach([['دسته','category.name'],['قیمت','price'],['موجودی','stock'],['عیار','18K'],['مدل سه‌بعدی','model_path']] as $row)<div class="compare-row"><b>{{ $row[0] }}</b>@foreach($products as $p)<span>@if($row[0]=='دسته'){{ optional($p->category)->name }}@elseif($row[0]=='قیمت'){{ number_format($p->price) }} تومان @elseif($row[0]=='موجودی'){{ $p->stock }} عدد @elseif($row[0]=='عیار')18K @else{{ $p->model_path?'فعال':'نمایش تصویری' }}@endif</span>@endforeach</div>@endforeach
<div class="compare-row compare-actions"><b>عملیات</b>@foreach($products as $p)<span><a class="gold-btn" href="{{ route('products.show',$p) }}">مشاهده</a><form method="post" action="{{ route('features.compare.remove',$p) }}">@csrf @method('DELETE')<button class="text-danger">حذف</button></form></span>@endforeach</div></div>@endif
@endsection
