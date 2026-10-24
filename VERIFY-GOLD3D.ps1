$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
Write-Host '=== Gold3D verification ===' -ForegroundColor Cyan
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP not found.' }
if (-not (Get-Command node -ErrorAction SilentlyContinue)) { throw 'Node not found.' }
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) { throw 'npm not found.' }
Write-Host 'PHP:' (php -v | Select-Object -First 1)
Write-Host 'Node:' (node -v)
Write-Host 'npm :' (npm -v)
Write-Host 'Checking PHP syntax...'
Get-ChildItem app,bootstrap,config,database,routes -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName | Out-Null }
Write-Host 'Checking JavaScript syntax...'
Get-ChildItem resources/js -Recurse -Filter *.js | ForEach-Object { node --check $_.FullName | Out-Null }
Write-Host 'Checking bundled 3D GLB models...'
$models = @('public/models3d/ring-classic.glb','public/models3d/ring-wedding.glb','public/models3d/ring-signature.glb')
foreach ($model in $models) { if (-not (Test-Path $model)) { throw "Missing 3D model: $model" } }
Write-Host 'Bundled GLB models: OK' -ForegroundColor Green
Write-Host 'Installing/building frontend...'
npm install --no-audit --no-fund
npm run build
Write-Host 'Clearing Laravel cache...'
php artisan optimize:clear
Write-Host 'Running migrations check...'
php artisan migrate:status
Write-Host 'Gold3D verification completed.' -ForegroundColor Green
