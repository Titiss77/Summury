$htdocs = (Resolve-Path (Join-Path $PSScriptRoot '..\htdocs')).Path
$root = $htdocs + '\'
$maxLines = 8000

$excludedExtensions = @('.ico', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.zip', '.pdf')
$excludedNames = @('.gitkeep', 'welcome_message.php', 'index.html', 'robots.txt')
$excludedPathRegex = '(\\|/)Views(\\|/)errors(\\|/)|(\\|/)Config(\\|/)(?!(Routes|Filters|Database|AuthRoutes)\.php$)'

$targets = @('app', 'public') | ForEach-Object { Join-Path $htdocs $_ } | Where-Object { Test-Path $_ }

# Nettoie les anciens fichiers context_part*.txt
Get-ChildItem -Path $PSScriptRoot -Filter 'context_part*.txt' -File -ErrorAction SilentlyContinue | Remove-Item -Force

$blocks = $targets | Get-ChildItem -File -Recurse -Force | Where-Object {
  ($excludedExtensions -notcontains $_.Extension.ToLower()) -and
  ($excludedNames -notcontains $_.Name) -and
  ($_.FullName -notmatch $excludedPathRegex)
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

$partIndex = 1
$currentBlocks = [System.Collections.Generic.List[string]]::new()
$currentLines = 0
$generatedFiles = [System.Collections.Generic.List[string]]::new()

function Write-PartFile {
  param(
    [string]$Content,
    [int]$Index,
    [int]$LineCount
  )
  $outPath = Join-Path $PSScriptRoot "context_part$Index.txt"
  $Content | Set-Content $outPath -Encoding UTF8
  $generatedFiles.Add("context_part$Index.txt ($LineCount lignes)")
}

foreach ($block in $blocks) {
  $lines = $block -split "\r?\n"
  $blockLineCount = $lines.Count

  # Cas où un seul fichier dépasse déjà la limite de lignes
  if ($blockLineCount -gt $maxLines) {
    if ($currentBlocks.Count -gt 0) {
      Write-PartFile -Content ($currentBlocks -join "`r`n`r`n") -Index $partIndex -LineCount $currentLines
      $partIndex++
      $currentBlocks.Clear()
      $currentLines = 0
    }

    for ($i = 0; $i -lt $blockLineCount; $i += $maxLines) {
      $end = [Math]::Min($i + $maxLines - 1, $blockLineCount - 1)
      $chunkLines = $lines[$i..$end]
      Write-PartFile -Content ($chunkLines -join "`r`n") -Index $partIndex -LineCount $chunkLines.Count
      $partIndex++
    }
    continue
  }

  # 1 ligne vide de séparation entre deux blocs
  $sepLines = if ($currentBlocks.Count -gt 0) { 1 } else { 0 }

  if (($currentLines + $sepLines + $blockLineCount) -gt $maxLines -and $currentBlocks.Count -gt 0) {
    Write-PartFile -Content ($currentBlocks -join "`r`n`r`n") -Index $partIndex -LineCount $currentLines
    $partIndex++
    $currentBlocks.Clear()
    $currentLines = 0
    $sepLines = 0
  }

  $currentBlocks.Add($block)
  $currentLines += $sepLines + $blockLineCount
}

if ($currentBlocks.Count -gt 0) {
  Write-PartFile -Content ($currentBlocks -join "`r`n`r`n") -Index $partIndex -LineCount $currentLines
}

Write-Host "Export terminé : $($blocks.Count) fichiers répartis dans $($generatedFiles.Count) fichier(s) :"
$generatedFiles | ForEach-Object { Write-Host " - $_" }