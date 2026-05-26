$ErrorActionPreference = 'Stop'

$root = Resolve-Path (Join-Path $PSScriptRoot '..')
$plainRoot = Join-Path $root 'php73'
$php = 'D:\phpstudy_pro\Extensions\php\php7.3.4nts\php.exe'

if (!(Test-Path $php)) {
    throw "PHP 7.3.4 executable not found at $php"
}

$requiredFiles = @(
    'index.php',
    'app.php',
    'config.php',
    'assets/style.css',
    'data/seed.json',
    'README-PHP73.txt'
)

foreach ($file in $requiredFiles) {
    $path = Join-Path $plainRoot $file
    if (!(Test-Path $path)) {
        throw "Missing PHP 7.3 plain project file: $file"
    }
}

$phpFiles = Get-ChildItem -Path $plainRoot -Filter '*.php' -Recurse
foreach ($file in $phpFiles) {
    $output = & $php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "PHP lint failed for $($file.FullName): $output"
    }
}

$appText = Get-Content -Raw -LiteralPath (Join-Path $plainRoot 'app.php')
$forbidden = @('Illuminate\', 'vendor/autoload.php', 'shell_exec', 'proc_open')
foreach ($token in $forbidden) {
    if ($appText.Contains($token)) {
        throw "Plain PHP app must not depend on Laravel, Illuminate, Composer autoload, or artisan. Found: $token"
    }
}

$indexText = Get-Content -Raw -LiteralPath (Join-Path $plainRoot 'index.php')
$markers = @(
    'edu-platform-title',
    'PHP 7.3.4',
    'backend/login',
    'resources',
    'questions',
    'announcements'
)
foreach ($marker in $markers) {
    if ($indexText -notmatch [Regex]::Escape($marker) -and $appText -notmatch [Regex]::Escape($marker)) {
        throw "Missing expected marker: $marker"
    }
}

$seed = Get-Content -Raw -LiteralPath (Join-Path $plainRoot 'data/seed.json')
foreach ($marker in @('"users"', '"resources"', '"announcements"', '"questions"', '"boards"', '"posts"')) {
    if ($seed -notmatch [Regex]::Escape($marker)) {
        throw "Seed data missing marker: $marker"
    }
}

Write-Host 'PHP 7.3 plain project contract checks passed.'
