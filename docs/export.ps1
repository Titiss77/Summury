$htdocs = (Resolve-Path (Join-Path $PSScriptRoot '..\htdocs')).Path
$root = $htdocs + '\'
$out = Join-Path $PSScriptRoot 'context.txt'

$excludedExtensions = @('.ico', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.zip', '.pdf')
$excludedNames = @('.gitkeep')

$targets = @('app', 'public') | ForEach-Object { Join-Path $htdocs $_ } | Where-Object { Test-Path $_ }

$blocks = $targets | Get-ChildItem -File -Recurse | Where-Object {
  ($excludedExtensions -notcontains $_.Extension.ToLower()) -and
  ($excludedNames -notcontains $_.Name)
} | Sort-Object FullName | ForEach-Object {
  $relPath = $_.FullName.Replace($root, '').Replace('\', '/')
  $lang = switch ($_.Extension.ToLower()) {
    '.php' { 'php' }
    '.js' { 'javascript' }
    '.css' { 'css' }
    '.html' { 'html' }
    '.json' { 'json' }
    '.sql' { 'sql' }
    default { '' }
  }
  $content = [string](Get-Content $_.FullName -Raw -Encoding UTF8)
  "## $relPath`r`n``````$lang`r`n$($content.TrimEnd())`r`n``````"
}

$blocks -join "`r`n`r`n" | Set-Content $out -Encoding UTF8
Write-Host "Fichier généré ($($blocks.Count) fichiers) : $out"