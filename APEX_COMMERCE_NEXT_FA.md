# Gold3D Apex Commerce — Storefront Upgrade

این نسخه تجربه مشتری را یک مرحله ارتقا می‌دهد:

- Wishlist واقعی متصل به دیتابیس
- Compare واقعی بر اساس session
- صفحه مقایسه چند محصول
- ثبت نظر مشتری و moderation در پنل مدیریت موجود
- Price Watch برای محصول
- صفحه محصول با بخش Product Intelligence
- نمایش نظرات تأییدشده
- اکشن‌های خرید / علاقه‌مندی / مقایسه در صفحه محصول
- دسترسی سریع علاقه‌مندی و مقایسه در هدر
- ظاهر جدید برای Wishlist و Compare
- responsive برای موبایل

## اجرا

```powershell
npm install
npm run build
php artisan migrate
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

این مرحله migration جدیدی لازم ندارد چون جدول‌های wishlist، comparison، price_alerts و reviews در نسخه قبلی وجود دارند.
