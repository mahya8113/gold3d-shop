$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
Write-Host '=== Gold3D Local Launcher ===' -ForegroundColor Cyan

@('bootstrap/cache','storage/framework/cache','storage/framework/sessions','storage/framework/views','storage/logs','database') | ForEach-Object { New-Item -ItemType Directory -Force -Path $_ | Out-Null }

if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP پیدا نشد. PHP 8.2+ را به PATH اضافه کن.' }
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) { throw 'Composer پیدا نشد. Composer را نصب یا به PATH اضافه کن.' }

$envPath = Join-Path $PWD '.env'
$envText = if (Test-Path $envPath) { Get-Content $envPath -Raw } else { '' }
if ($envText -match '(?m)^DB_CONNECTION=mysql\s*$' -or $envText -match '(?m)^DB_HOST=127\.0\.0\.1\s*$') {
    $backup = ".env.mysql-backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    Copy-Item $envPath $backup -Force
    Write-Host "تنظیمات MySQL قبلی در $backup نگه داشته شد." -ForegroundColor Yellow
    Remove-Item $envPath -Force
}
if (-not (Test-Path $envPath)) { Copy-Item '.env.example' '.env' -Force }

$envText = Get-Content $envPath -Raw
function Set-EnvValue([string]$text,[string]$key,[string]$value) {
    $line = "$key=$value"
    $pattern = "(?m)^" + [regex]::Escape($key) + ".*$"
    if ($text -match $pattern) { return [regex]::Replace($text,$pattern,[System.Text.RegularExpressions.MatchEvaluator]{ param($m) $line }) }
    return ($text.TrimEnd() + "`r`n" + $line + "`r`n")
}
$envText = Set-EnvValue $envText 'APP_ENV' 'local'
$envText = Set-EnvValue $envText 'APP_DEBUG' 'true'
$envText = Set-EnvValue $envText 'APP_URL' 'http://127.0.0.1:8000'
$envText = Set-EnvValue $envText 'DB_CONNECTION' 'sqlite'
$envText = Set-EnvValue $envText 'DB_DATABASE' 'database/database.sqlite'
$envText = Set-EnvValue $envText 'SESSION_DRIVER' 'file'
$envText = Set-EnvValue $envText 'SESSION_SECURE_COOKIE' 'false'
$envText = Set-EnvValue $envText 'CACHE_STORE' 'file'
$envText = Set-EnvValue $envText 'QUEUE_CONNECTION' 'sync'
$envText = Set-EnvValue $envText 'GOLD_PRICE_PROVIDER' 'tgju'
$envText = Set-EnvValue $envText 'GOLD_PRICE_18_URL' 'https://www.tgju.org/profile/geram18/today'
$envText = Set-EnvValue $envText 'GOLD_PRICE_24_URL' 'https://www.tgju.org/profile/geram24/today'
$envText = Set-EnvValue $envText 'GOLD_PRICE_OUNCE_URL' 'https://www.tgju.org/profile/ons/today'
$envText = Set-EnvValue $envText 'GOLD_PRICE_REFRESH_SECONDS' '60'
$envText = Set-EnvValue $envText 'GOLD_PRICE_SNAPSHOT_MINUTES' '5'
$envText = Set-EnvValue $envText 'GOLD_PRICE_FALLBACK_TOMAN' '8500000'
$envText = Set-EnvValue $envText 'ZARINPAL_SANDBOX' 'true'
$envText = Set-EnvValue $envText 'ZARINPAL_MERCHANT_ID' ''
Set-Content -Path $envPath -Value $envText -Encoding UTF8

$dbPath = Join-Path $PWD 'database/database.sqlite'
if (-not (Test-Path $dbPath)) { New-Item -ItemType File -Path $dbPath | Out-Null }

$sqliteCheck = php -r "echo extension_loaded('pdo_sqlite') ? 'yes' : 'no';"
if ($sqliteCheck.Trim() -ne 'yes') { throw 'pdo_sqlite در PHP فعال نیست. در php.ini اکستنشن pdo_sqlite را فعال کن و دوباره اجرا کن.' }

if (-not (Test-Path 'vendor/autoload.php')) { Write-Host 'vendor وجود ندارد؛ composer install اجرا می‌شود...' -ForegroundColor Cyan; composer install --no-interaction }

php artisan key:generate --force
php artisan optimize:clear
php artisan migrate --seed --force

if (Get-Command npm -ErrorAction SilentlyContinue) {
    if (-not (Test-Path 'node_modules')) { Write-Host 'node_modules وجود ندارد؛ npm install اجرا می‌شود...' -ForegroundColor Cyan; npm install }
    npm run build
} else { Write-Host 'npm پیدا نشد؛ build فرانت‌اند انجام نشد.' -ForegroundColor Yellow }

Write-Host ''
Write-Host 'Gold3D آماده اجراست.' -ForegroundColor Green
Write-Host 'Open: http://127.0.0.1:8000' -ForegroundColor Green
Write-Host 'این پنجره را باز نگه دار. برای توقف Ctrl+C.' -ForegroundColor Yellow
php artisan serve --host=127.0.0.1 --port=8000
