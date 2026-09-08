# Verify theme package integrity before upload.
# Usage: powershell -ExecutionPolicy Bypass -File scripts/verify-theme.ps1 [-ZipPath path\to\wpstack-child.zip]

param(
    [string]$ZipPath = ""
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem

$themeRoot = Split-Path -Parent $PSScriptRoot
if (-not $ZipPath) {
    $ZipPath = Join-Path (Split-Path -Parent $themeRoot) 'dist\wpstack-child.zip'
}

if (-not (Test-Path $ZipPath)) {
    Write-Error "ZIP not found: $ZipPath`nRun scripts/build-theme-zip.ps1 first."
}

# Files required by functions.php (must exist in package)
$required = @(
    'style.css',
    'functions.php',
    'includes/class-ipo-calendar.php',
    'includes/class-ipo-event.php',
    'includes/class-ipo-program.php',
    'includes/custom-functions.php',
    'includes/taxonomy-radio-buttons.php',
    'includes/wp_insert_attachment_from_url.php',
    'includes/event-permalink.php',
    'includes/ipo-shortcodes.php',
    'includes/ipo-bidirectional.php',
    'includes/serie-category-programs.php',
    'includes/ajax/ajax_get_events/ajax_get_events.php',
    'includes/ajax/ajax_get_events/ajax_get_events.js',
    'includes/ajax/ajax_get_calendar_events/ajax_get_calendar_events.php',
    'includes/ajax/ajax_get_calendar_events/ajax_get_calendar_events.js',
    'includes/ajax/ajax_get_month/ajax_get_month.php',
    'includes/ajax/ajax_get_month/ajax_get_month.js',
    'includes/ajax/ajax_import_batch/ajax_import_batch.php',
    'includes/ajax/ajax_process_posts/ajax_process_posts.php',
    'parts/calendar-full.php',
    'assets/scripts/ipo-custom.js',
    'assets/styles/ipo-custom.css'
)

$zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
$entries = @{}
foreach ($e in $zip.Entries) {
    $key = ($e.FullName -replace '\\', '/').TrimEnd('/')
    if ($key) { $entries[$key] = $true }
}
$zip.Dispose()

$missing = @()
$gitFound = @()
foreach ($path in $entries.Keys) {
    if ($path -match '(^|/)\.git(/|$)') {
        $gitFound += $path
    }
}
foreach ($file in $required) {
    $norm = $file -replace '\\', '/'
    if (-not $entries.ContainsKey($norm)) {
        $missing += $norm
    }
}

$zipFile = Get-Item $ZipPath
Write-Host ""
Write-Host "=== Theme package verification ===" -ForegroundColor Cyan
Write-Host "ZIP: $($zipFile.FullName)"
Write-Host "Size: $([math]::Round($zipFile.Length / 1MB, 2)) MB"
Write-Host "Total entries: $($entries.Count)"
Write-Host ""

if ($gitFound.Count -gt 0) {
    Write-Host "FAIL: .git files found in ZIP ($($gitFound.Count))" -ForegroundColor Red
    $gitFound | Select-Object -First 5 | ForEach-Object { Write-Host "  $_" }
    exit 1
} else {
    Write-Host "OK: No .git in package" -ForegroundColor Green
}

if ($missing.Count -gt 0) {
    Write-Host "FAIL: Missing required files ($($missing.Count)):" -ForegroundColor Red
    $missing | ForEach-Object { Write-Host "  $_" }
    exit 1
} else {
    Write-Host "OK: All $($required.Count) required files present" -ForegroundColor Green
}

Write-Host ""
Write-Host "Package is ready for server upload." -ForegroundColor Green
exit 0
