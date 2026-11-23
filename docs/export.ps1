$htdocs = (Resolve-Path (Join-Path $PSScriptRoot '..\htdocs')).Path
$appDir = if (Test-Path (Join-Path $htdocs 'app')) { Join-Path $htdocs 'app' } else { $htdocs }
$root = $htdocs + '\'
$out = Join-Path $PSScriptRoot 'context.md'

@(
  (Join-Path $appDir 'Controllers'),
  (Join-Path $appDir 'Views'),
  (Join-Path $appDir 'Config\Routes.php'),
  (Join-Path $appDir 'Models')
) | Get-ChildItem -File -Recurse | ForEach-Object {
  "## $($_.FullName.Replace($root, ''))"
  '```php'
  Get-Content $_.FullName -Encoding UTF8
  '```'
} | Set-Content $out -Encoding UTF8

Write-Host "Fichier généré : $out"