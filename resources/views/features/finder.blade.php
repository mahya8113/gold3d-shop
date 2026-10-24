@extends('layouts.app')
@section('title','Gold3D | مشاور خرید هوشمند')
@section('content')
<section class="finder-page">
  <div class="finder-hero">
    <span class="eyebrow">GOLD3D SMART FINDER</span>
    <h1>جواهر مناسب خودت را<br><strong>پیدا کن.</strong></h1>
    <p>بودجه و سلیقه‌ات را بگو؛ Gold3D محصولات موجود را امتیازدهی می‌کند و بهترین گزینه‌ها را همراه با دلیل پیشنهاد می‌دهد.</p>
  </div>

  <form id="finder-form" class="finder-card">
    @csrf
    <div class="finder-grid">
      <label>بودجه (تومان)<input id="finder-budget" name="budget" type="number" min="100000" step="100000" placeholder="مثلاً 80000000" required></label>
      <label>دسته‌بندی<select name="category"><option value="">همه دسته‌ها</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
      <label>عیار<select name="karat"><option value="">مهم نیست</option><option value="18">۱۸ عیار</option><option value="24">۲۴ عیار</option></select></label>
      <label>سبک موردنظر<input name="style" maxlength="40" placeholder="مثلاً کلاسیک، مینیمال..." /></label>
    </div>
    <button class="finder-submit" type="submit">پیدا کردن بهترین انتخاب‌ها ✦</button>
  </form>

  <section id="finder-results" class="finder-results" aria-live="polite"></section>
</section>
<script>
(() => {
 const form=document.getElementById('finder-form'), out=document.getElementById('finder-results');
 form.addEventListener('submit', async (e) => {
   e.preventDefault(); out.innerHTML='<div class="finder-loading">در حال تحلیل محصولات موجود…</div>';
   try {
     const r=await fetch('{{ route('features.finder.recommend') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(Object.fromEntries(new FormData(form)))});
     const data=await r.json(); if(!r.ok) throw new Error(data.message||'خطا');
     if(!data.results.length){out.innerHTML='<div class="finder-empty"><h3>فعلاً تطابق مناسبی پیدا نشد.</h3><p>بودجه را کمی افزایش بده یا فیلترهای دسته و عیار را بازتر کن.</p></div>';return;}
     out.innerHTML='<div class="finder-head"><div><span>SMART MATCH</span><h2>پیشنهادهای مناسب شما</h2></div><b>'+data.count+' انتخاب</b></div><div class="finder-cards">'+data.results.map((x,i)=>{
       const img=x.image || 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=900&q=85';
       return '<article class="finder-product"><div class="finder-rank">#'+(i+1)+'</div><img src="'+img+'" alt="'+x.name+'" loading="lazy"><div class="finder-product-body"><div class="finder-score">MATCH '+x.score+'%</div><h3>'+x.name+'</h3><p class="finder-price">'+new Intl.NumberFormat('fa-IR').format(x.price)+' تومان</p><div class="finder-reasons">'+x.reasons.map(r=>'<span>✓ '+r+'</span>').join('')+'</div><div class="finder-actions"><a href="/products/'+encodeURIComponent(x.slug)+'">مشاهده محصول</a>'+(x.model3d?'<a href="{{ route('features.hub') }}">مشاهده 3D</a>':'')+'</div></div></article>';
     }).join('')+'</div>';
   } catch(err){out.innerHTML='<div class="finder-empty"><h3>اتصال مشاور برقرار نشد.</h3><p>دوباره تلاش کن؛ اگر خطا باقی ماند، پیام خطا را برای بررسی بفرست.</p></div>';}
 });
})();
</script>
@endsection
