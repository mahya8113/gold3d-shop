@extends('layouts.app')
@section('title','استودیو 3D | Gold3D')
@section('content')
<section class="studio-shell">
  <div class="studio-hero-bar">
    <div>
      <span class="eyebrow">GOLD3D · VIRTUAL ATELIER</span>
      <h1>طراحی کن، بچرخان، قبل از خرید ببین.</h1>
      <p>یک استودیوی سه‌بعدی برای انتخاب مدل، رنگ طلا، سنگ و وزن؛ با پیش‌نمایش زنده و برآورد قیمت.</p>
    </div>
    <div class="studio-hero-actions"><span class="live-dot">● LIVE 3D</span><a class="gold-btn" href="{{ route('products') }}">بازگشت به فروشگاه ←</a></div>
  </div>

  <div class="studio-workbench">
    <aside class="studio-sidebar studio-left">
      <div class="studio-tabs">
        <button class="active" type="button">مدل‌ها</button>
        <button type="button">جواهرات</button>
        <button type="button">متریال</button>
      </div>

      <div class="studio-section-title">نوع زیور</div>
      <div class="studio-choice-list">
        <button type="button" class="studio-choice active" data-type="ring"><span>◯</span> انگشتر</button>
        <button type="button" class="studio-choice" data-type="necklace"><span>⌁</span> گردنبند</button>
        <button type="button" class="studio-choice" data-type="bracelet"><span>◌</span> دستبند</button>
        <button type="button" class="studio-choice" data-type="earring"><span>♢</span> گوشواره</button>
      </div>

      <div class="studio-section-title" data-models-title>مدل‌های انگشتر</div>
      <div class="studio-models"></div>
    </aside>

    <section class="studio-stage-card">
      <div class="stage-toolbar">
        <span id="stage-product-name">Classic Gold · 18K</span>
        <div>
          <button id="stage-reset" type="button" title="بازنشانی">↻</button>
          <button id="stage-fullscreen" type="button" title="تمام صفحه">⛶</button>
          <button id="stage-auto" type="button" title="افکت نمایش">⌾</button>
        </div>
      </div>

      <div id="ring-preview" class="studio-3d-stage real-ring-stage studio-photo-stage" data-studio-3d>
        <div class="stage-grid"></div>
        <div class="stage-light light-a"></div>
        <div class="stage-light light-b"></div>
        <div class="studio-3d-loading" id="studio-3d-loading" hidden>
          <span class="studio-loader-ring"></span>
          <strong>در حال بارگذاری عکس...</strong>
          <small>قابل چرخش · قابل بزرگنمایی</small>
        </div>
      </div>

      <div class="stage-bottom">
        <div><b>عکس واقعی زیور</b><span>DRAG · ROTATE · ZOOM</span></div>
        <input id="stage-zoom" type="range" min="0" max="100" value="0">
        <span>ZOOM</span>
      </div>
    </section>

    <aside class="studio-sidebar studio-right">
      <div class="studio-right-tabs">
        <button class="active" type="button">تنظیمات</button>
        <button type="button">جزئیات</button>
        <button type="button">خروجی</button>
      </div>

      <div class="control-block">
        <h3>جنس و رنگ طلا</h3>
        <div class="swatches">
          <button type="button" data-color="yellow" class="metal active"><i style="background:#d8a847"></i><span>طلای زرد</span></button>
          <button type="button" data-color="white" class="metal"><i style="background:#d9dce2"></i><span>طلای سفید</span></button>
          <button type="button" data-color="rose" class="metal"><i style="background:#c98982"></i><span>رزگلد</span></button>
        </div>
      </div>

      <div class="control-block">
        <h3>عیار</h3>
        <input type="hidden" id="gold-karat" value="18">
        <div class="segmented"><button type="button" data-karat="18" class="active">18K</button><button type="button" data-karat="24">24K</button></div>
      </div>

      <div class="control-block">
        <h3>سنگ مرکزی</h3>
        <input type="hidden" id="gem" value="none">
        <div class="gem-grid">
          <button type="button" data-gem="none" class="active">بدون سنگ</button>
          <button type="button" data-gem="zircon">زیرکونیا</button>
          <button type="button" data-gem="sapphire">یاقوت</button>
          <button type="button" data-gem="diamond">الماس</button>
        </div>
      </div>

      <div class="control-block">
        <h3>وزن تقریبی</h3>
        <div class="range-line"><input id="gold-weight" type="range" min="1" max="30" step=".1" value="4"><output id="weight-output">4 گرم</output></div>
      </div>

      <div class="control-block advanced-settings">
        <h3>تنظیمات پیشرفته</h3>
        <label>نوع زیور<select id="design-type"><option value="ring">انگشتر</option><option value="necklace">گردنبند</option><option value="bracelet">دستبند</option><option value="earring">گوشواره</option></select></label>
        <label>سایز انگشتر<input id="ring-size" type="number" min="8" max="30" value="18"></label>
        <input type="hidden" id="gold-color" value="yellow">
      </div>

      <div class="studio-price"><span>برآورد فعلی</span><strong id="estimate">در حال محاسبه...</strong><small>بر اساس نرخ ثبت‌شده طلا + اجرت + سود + سنگ</small></div>
      <button id="save-design" class="gold-btn studio-render" type="button">◈ ذخیره طرح</button>
      <p id="studio-message" class="studio-message">مدل، رنگ، عیار، سنگ و وزن را تغییر بده؛ پیش‌نمایش و قیمت بلافاصله به‌روزرسانی می‌شوند.</p>
    </aside>
  </div>

  <section class="studio-benefits">
    <div><b>◉</b><span><strong>REAL PHOTO</strong>عکس واقعی هر زیور</span></div><div><b>↻</b><span><strong>ROTATE</strong>چرخش با ماوس یا انگشت</span></div><div><b>⚙</b><span><strong>شخصی‌سازی</strong>رنگ، عیار، سنگ و وزن</span></div><div><b>◇</b><span><strong>قیمت‌گذاری</strong>اتصال به نرخ طلا</span></div><div><b>▣</b><span><strong>زرین‌پال</strong>پرداخت آنلاین</span></div><div><b>◌</b><span><strong>پشتیبانی</strong>پاسخ‌گویی 24 ساعته</span></div>
  </section>
</section>

<section class="studio-recommendations">
 <div class="section-title"><div><span class="eyebrow dark">SMART JEWELRY MATCH</span><h2>پیشنهادهای هوشمند برای تو</h2></div></div>
 <div class="studio-recommend-grid"><div class="recommend-card"><h3>مشاور انتخاب</h3><p>دسته مورد علاقه‌ات را انتخاب کن تا محصولات نزدیک به طرح فعلی پیشنهاد شوند.</p><select id="recommend-category"><option value="">همه دسته‌ها</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select><button id="recommend-btn" class="outline-btn" type="button">پیشنهاد بده</button><div id="recommend-results"></div></div><div class="recommend-visual"><img src="https://images.unsplash.com/photo-1567523977592-7959bc5df51e?auto=format&fit=crop&fm=jpg&q=88&w=1200" alt="عکس واقعی انگشتر"><div><span>ROYAL COLLECTION</span><strong>جزئیات واقعی، انتخاب مطمئن</strong></div></div></div>
</section>

<section class="studio-custom-order card">
 <div><span class="eyebrow dark">BESPOKE JEWELRY</span><h2>طرحی که هیچ‌کس مثل آن را ندارد</h2><p>اگر مدل اختصاصی می‌خواهی، مشخصات را ثبت کن تا برای ساخت سفارشی بررسی شود.</p></div>
 @auth<form method="post" action="{{ route('features.custom-order') }}">@csrf<label>عنوان طرح<input name="title" required maxlength="120" placeholder="مثلاً انگشتر نامزدی سفارشی"></label><label>توضیحات<textarea name="brief" rows="3" required placeholder="سنگ، رنگ طلا، وزن تقریبی و جزئیات موردنظر..."></textarea></label><button class="gold-btn">ارسال درخواست ساخت ←</button></form>@else<div class="login-note">برای ثبت سفارش ساخت اختصاصی ابتدا وارد حساب کاربری شوید.</div>@endauth
</section>

<script>
window.gold3dFeatureConfig={ratePerGram:@json($rate?->price_per_gram ?? 0),labor:@json($rate?->labor_percent ?? 12),profit:@json($rate?->profit_percent ?? 8),tax:@json($rate?->tax_percent ?? 0),saveUrl:@json(route('features.design.save')),recommendUrl:@json(route('features.recommendations'))};
</script>
@endsection
