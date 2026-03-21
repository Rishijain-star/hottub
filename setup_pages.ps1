# Run this script from: C:\xampp\htdocs\HotTub
# Open PowerShell in that folder and run: .\setup_pages.ps1

$source = "resources\views\pages\hot-tubs.blade.php"
$pages  = @(
    "swim-spas",
    "services",
    "parts",
    "brands",
    "find-dealer",
    "care-guide",
    "faq",
    "login",
    "register"
)

foreach ($page in $pages) {
    $dest = "resources\views\pages\$page.blade.php"
    if (!(Test-Path $dest)) {
        Copy-Item $source $dest
        Write-Host "✅ Created: $dest" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Already exists: $dest" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "🎉 All pages ready! Run: php artisan serve" -ForegroundColor Cyan
