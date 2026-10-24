# Gold3D Shop — Commercial-ready Laravel application source

فروشگاه فارسی جواهرات با کاتالوگ، فیلتر، مدل سه‌بعدی، سبد خرید session، ثبت سفارش، پرداخت زرین‌پال، حساب مشتری و پنل مدیریت.

> این بسته سورس برنامه و فایل‌های پیکربندی را شامل می‌شود؛ برای اجرای واقعی باید وابستگی‌ها نصب، کلیدها و درگاه تنظیم، تست‌ها اجرا و بررسی امنیت/حقوقی و پذیرش درگاه انجام شود. هیچ فروشگاه آنلاین بدون این مراحل را نمی‌توان «تضمین‌شده آماده انتشار» دانست.

## نصب
1. PHP 8.2+، Composer، MySQL 8+ و Node 20+ نصب کنید.
2. `composer install`
3. `cp .env.example .env` و دیتابیس و تنظیمات را تکمیل کنید.
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `npm install && npm run build`
7. `php artisan serve`

## مدیر
بعد از ثبت‌نام کاربر، از طریق SQL یا Tinker مقدار `is_admin` را برای حساب موردنظر true کنید. دسترسی admin را برای حساب‌های عمومی فعال نکنید.

## پرداخت
مبالغ به تومان در رابط کاربری‌اند و به ریال برای زرین‌پال تبدیل می‌شوند. مقدارها و قرارداد فعلی درگاه را با حساب پذیرنده و مستندات جاری تطبیق دهید؛ ابتدا sandbox/تست انجام دهید. Callback، مبلغ، مالک سفارش، authority و وضعیت تأیید می‌شوند. قبل از عملیات واقعی، آزمون پذیرش و بررسی امنیتی لازم است.

## مدل سه‌بعدی
فایل‌های دارای مجوز استفاده را در `public/models3d/` قرار دهید. مدل‌ها باید GLB باشند. viewer از Three.js و GLTFLoader استفاده می‌کند.


## نسخه جامع توسعه‌یافته
صفحه `/studio` برای پیکربندی طرح، `/services` برای پشتیبانی/کارت هدیه آزمایشی/حکاکی/دوربین و `/admin/features` برای عملیات نرخ طلا اضافه شده‌اند. راهنمای انتشار و موارد باقی‌مانده در `DEPLOYMENT_CHECKLIST_FA.md` آمده است. قابلیت‌های پرداخت واقعی، WebAR و AI آموزش‌دیده نیازمند تکمیل اتصال‌ها و آزمون پذیرش هستند.

## Gold3D جامع — دامنه قابلیت‌ها
- فروشگاه فارسی RTL، فهرست/جزئیات، سبد، سفارش، حساب کاربری و پنل مدیریت پایه
- اتصال اولیه درگاه زرین‌پال با تنظیم merchant و sandbox
- استودیو تنظیمات جواهر، تخمین نرخ‌محور، ذخیره طرح و درخواست ساخت سفارشی
- علاقه‌مندی، هشدار قیمت، مقایسه و پیشنهادگر قاعده‌محور
- مدیریت نرخ مبنا/اجرت/سود/مالیات با نگهداری تاریخچه نرخ
- تیکت پشتیبانی، نمونه کارت هدیه، مدل داده گواهی اصالت و صفحه استعلام
- ابزار حکاکی نمایشی، دسترسی دوربین با رضایت کاربر، PWA shell
- چک‌لیست استقرار در `DEPLOYMENT_CHECKLIST_FA.md`

## شروع محلی
1. `composer install`
2. کپی `.env.example` به `.env` و ساخت دیتابیس
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `npm install`
6. `npm run build`
7. `php artisan serve`

برای اولین حساب مدیر، پس از ثبت‌نام در محیط محلی با Tinker مقدار `is_admin` را برای حساب خود فعال کنید. هرگز این کار را برای حساب عمومی یا در محیط تولید بدون کنترل دسترسی انجام ندهید.

## محدودیت‌های باقی‌مانده
این خروجی سورس کامل‌تر برای توسعه و آزمون است، اما فروشگاه واقعی تا انجام آزمون end-to-end، تست sandbox زرین‌پال، بازبینی امنیتی، تکمیل چرخه بازپرداخت/مرجوعی و فعال‌سازی سرویس‌های اعلان/نرخ طلا آماده انتشار محسوب نمی‌شود. پیشنهادگر فعلی ML نیست؛ WebAR نیز فعلاً دسترسی دوربین را آزمایش می‌کند و tracking سه‌بعدی نیازمند توسعه است. از نرخ‌ها و قیمت‌های نمونه برای فروش واقعی استفاده نکنید.

## اجرای ساده روی Windows (بدون MySQL و Docker)
برای دمو و اجرای محلی، `run-local.ps1` تنظیمات MySQL قبلی را یک‌بار backup می‌کند و پروژه را روی SQLite محلی تنظیم می‌کند. سپس در صورت نیاز Composer/NPM را نصب می‌کند، کش Laravel را آماده می‌کند، migration/seed و build را اجرا می‌کند و سرور را روی `http://127.0.0.1:8000` بالا می‌آورد.

در PowerShell ریشه پروژه اجرا کنید:
`Set-ExecutionPolicy -Scope Process Bypass`
سپس:
`./run-local.ps1`

این حالت برای دمو/پورتفولیو است و جایگزین تنظیمات MySQL و زیرساخت production نیست.


## Gold3D visual refresh
این نسخه شامل رابط RTL لوکس، ۱۲ محصول نمونه با عکس‌های واقعی آنلاین و viewer سه‌بعدی Three.js است. وقتی محصول GLB/GLTF نداشته باشد، viewer عمداً به عکس واقعی fallback می‌کند و مدل سفید/procedural تولید نمی‌کند. برای seed کردن محصولات: `php artisan db:seed --force` و سپس `npm run build`.


## Gold3D Obsidian upgrade
- Black/obsidian + gold visual system with a real Three.js ring viewer on the home page.
- 12 seeded jewelry samples with a real-photo fallback; real 360° interaction is enabled when a licensed GLB/GLTF model is provided.
- Live 18K/24K gold and global ounce market panel sourced from TGJU pages, with a server-side cache and local snapshots.
- `/api/gold-market` returns the latest market snapshot and Gold3D chart history.
- ZarinPal v4 request/verify flow is wired in `app/Services/ZarinpalService.php`. Keep sandbox enabled for local testing; set a real merchant UUID and public HTTPS callback before live transactions.
- The market feed is an external dependency; if it is unreachable, the storefront shows the last stored rate rather than pretending the price is live.

### Local run
1. Extract the ZIP to a writable folder.
2. Open the project root in VS Code.
3. In PowerShell run `Set-ExecutionPolicy -Scope Process Bypass` if needed.
4. Run `./run-local.ps1`.
5. Open `http://127.0.0.1:8000`.

The local launcher uses SQLite so Docker/MySQL are not required for the demo.

## آخرین نسخه اصلاح‌شده
برای این نسخه، `CHECKLIST_FIXED_FA.md` فهرست اصلاحات را دارد. مهم‌ترین نکته این است که Vite خروجی را در `public/build` می‌سازد و همه ورودی‌های موجود در layout را پوشش می‌دهد. برای اجرای کامل روی Windows می‌توانی مستقیماً `run-local.ps1` را اجرا کنی.

### Smart Jewelry Finder
Gold3D includes a customer-facing `/finder` experience that recommends available jewelry based on budget, category, karat, style text, inventory, and 3D availability. The recommendation score is deterministic and rule-based; no external AI API is required.
# gold3d-shop
