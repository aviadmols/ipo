# Build a WordPress-ready theme ZIP (no .git, no dev files).
# Usage: powershell -ExecutionPolicy Bypass -File scripts/build-theme-zip.ps1

$ErrorActionPreference = 'Stop'

$themeRoot = Split-Path -Parent $PSScriptRoot
$themeSlug = Split-Path -Leaf $themeRoot
$distDir = Join-Path (Split-Path -Parent $themeRoot) 'dist'
$stagingDir = Join-Path $distDir $themeSlug
$zipPath = Join-Path $distDir "$themeSlug.zip"
$distignorePath = Join-Path $themeRoot '.distignore'

if (-not (Test-Path $distignorePath)) {
    Write-Error ".distignore not found at $distignorePath"
}

# Read exclusion patterns
$excludePatterns = Get-Content $distignorePath |
    ForEach-Object { $_.Trim() } |
    Where-Object { $_ -and -not $_.StartsWith('#') }

function Should-Exclude {
    param([string]$RelativePath)

    $normalized = $RelativePath -replace '\\', '/'
    foreach ($pattern in $excludePatterns) {
        $p = $pattern -replace '\\', '/'
        if ($p -match '[\*\?]') {
            if ($normalized -like $p) { return $true }
            $base = Split-Path -Leaf $normalized
            if ($base -like $p) { return $true }
        } elseif ($normalized -eq $p -or $normalized.StartsWith("$p/")) {
            return $true
        }
    }
    return $false
}

# Safety: never include git metadata
if (Test-Path (Join-Path $themeRoot '.git')) {
    Write-Warning "Removing local .git before packaging..."
    Remove-Item -Recurse -Force (Join-Path $themeRoot '.git')
}

if (Test-Path $stagingDir) {
    Remove-Item -Recurse -Force $stagingDir
}
New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

$copied = 0
$skipped = 0

Get-ChildItem -Path $themeRoot -Recurse -Force | ForEach-Object {
    $relative = $_.FullName.Substring($themeRoot.Length).TrimStart('\', '/')
    if (-not $relative) { return }

    if (Should-Exclude $relative) {
        $script:skipped++
        return
    }

    $dest = Join-Path $stagingDir $relative
    if ($_.PSIsContainer) {
        if (-not (Test-Path $dest)) {
            New-Item -ItemType Directory -Path $dest -Force | Out-Null
        }
    } else {
        $destParent = Split-Path -Parent $dest
        if (-not (Test-Path $destParent)) {
            New-Item -ItemType Directory -Path $destParent -Force | Out-Null
        }
        Copy-Item -Path $_.FullName -Destination $dest -Force
        $script:copied++
    }
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($stagingDir, $zipPath)

# Verify no .git slipped in
$zip = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$gitEntries = $zip.Entries | Where-Object { $_.FullName -match '(^|/)\.git(/|$)' }
$zip.Dispose()

if ($gitEntries) {
    Remove-Item -Force $zipPath
    Write-Error "ZIP validation failed: .git entries found. Build aborted."
}

# Run integrity check
& (Join-Path $PSScriptRoot 'verify-theme.ps1') -ZipPath $zipPath
if ($LASTEXITCODE -ne 0) {
    Remove-Item -Force $zipPath -ErrorAction SilentlyContinue
    Write-Error "Build aborted: package verification failed."
}

Write-Host ""
Write-Host "Theme package ready:" -ForegroundColor Green
Write-Host "  $zipPath"
Write-Host "  Files copied: $copied | Skipped: $skipped"
Write-Host ""
Write-Host "Upload this ZIP via WordPress: Appearance > Themes > Add New > Upload Theme"
