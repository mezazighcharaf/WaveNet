# Script to remove all comments from PHP files

# Get all PHP files in the current directory and subdirectories
$phpFiles = Get-ChildItem -Path . -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    Write-Host "Processing file: $($file.FullName)"

    # Read the file content
    $content = Get-Content -Path $file.FullName -Raw

    # Remove multi-line comments (/**/ style and DocBlocks)
    $content = $content -replace '/\*[\s\S]*?\*/', ''

    # Remove single-line comments (// style) but not in URLs with http://
    $content = $content -replace '(?<!:)//.*', ''

    # Remove inline comments after statements
    $content = $content -replace '(.*?)\s+//.*', '$1'

    # Remove any extra blank lines created
    $content = $content -replace '(\r?\n){2,}', "`r`n`r`n"
    
    # Write the modified content back to the file
    Set-Content -Path $file.FullName -Value $content -NoNewline
    
    Write-Host "Comments removed from $($file.FullName)"
}

Write-Host "All done! Comments removed from all PHP files." 