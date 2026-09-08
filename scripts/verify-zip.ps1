Add-Type -AssemblyName System.IO.Compression.FileSystem
$path = Join-Path (Split-Path -Parent $PSScriptRoot) '..\dist\wpstack-child.zip'
$z = [System.IO.Compression.ZipFile]::OpenRead($path)
$z.Entries | Where-Object { $_.FullName -match 'includes' } | ForEach-Object { $_.FullName }
Write-Host "TOTAL includes: $(($z.Entries | Where-Object { $_.FullName -match 'includes' }).Count)"
$z.Dispose()
