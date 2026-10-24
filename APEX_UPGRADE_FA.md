# Gold3D Apex — ارتقای لایه ظاهر و باطن

این نسخه روی پایه‌ی `Gold3D-Real-Photos-Ultra` ساخته شده است.

## ظاهر
- رندر سه‌بعدی حرفه‌ای‌تر با Three.js
- نورپردازی چندمنبعی، متریال Physical، سایه و tone mapping
- Bloom با EffectComposer
- ذرات نور بسیار سبک و حرکت نرم
- fallback سه‌بعدی procedural در صورت نبود فایل GLB
- micro-interaction، reveal on scroll و حالت reduced-motion
- کارت‌های محصول و ویترین با hover و depth بیشتر
- بخش معماری فنی برای نمایش قابلیت‌های موتور فروشگاه

## باطن
- API سلامت واقعی: `/api/health` با بررسی DB و cache
- API کاتالوگ: `/api/catalog` با search/filter/sort/pagination
- rate limit روی APIهای عمومی
- cache کوتاه‌مدت برای کاتالوگ و بازار طلا
- eager loading دسته‌ها برای کاهش queryهای اضافه
- فیلتر قیمت، موجودی و مرتب‌سازی در کاتالوگ
- indexهای دیتابیس برای مسیرهای پرتکرار
- هدرهای امنیتی تکمیلی
- بررسی تطابق مبلغ پرداخت زرین‌پال با مبلغ سفارش
- پاک‌سازی سبد خرید فقط بعد از دریافت authority موفق زرین‌پال

## اجرا
```powershell
npm install
npm run build
php artisan migrate
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

بعد:
`http://127.0.0.1:8000`

### تست health
`http://127.0.0.1:8000/api/health`

باید JSON شامل `ok`, `database`, `cache` و `latency_ms` ببینی.

## نکته تصاویر واقعی
تصاویر جواهرات این نسخه همچنان از URLهای واقعی Pexels/Unsplash استفاده می‌کنند؛ بنابراین برای نمایش آن‌ها اینترنت لازم است. برای نسخه کاملاً آفلاین باید فایل‌های تصویری واقعی داخل `public/images/real` قرار بگیرند و seeder به مسیرهای محلی تغییر کند.

## نکته پرداخت
زرین‌پال هنوز برای تراکنش واقعی به Merchant ID واقعی، callback عمومی HTTPS و تنظیم production نیاز دارد. کد فعلی برای sandbox/local آماده‌تر شده ولی نباید بدون این تنظیمات به‌عنوان درگاه production نهایی تلقی شود.
