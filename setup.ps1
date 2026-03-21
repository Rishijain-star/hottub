# HotTub Buyer - Auto Setup Script (Fixed - No Emoji)
# Usage: powershell -ExecutionPolicy Bypass -File setup.ps1

$ROOT = "C:\xampp\htdocs\HotTub"

Write-Host "================================================"
Write-Host " HotTub Buyer - Creating all panel files..."
Write-Host "================================================"

# CREATE FOLDERS
Write-Host "[1/5] Creating folders..."
$folders = @(
    "$ROOT\resources\views\layouts",
    "$ROOT\resources\views\components",
    "$ROOT\resources\views\admin",
    "$ROOT\resources\views\dealer",
    "$ROOT\app\Http\Controllers\Admin",
    "$ROOT\app\Http\Controllers\Dealer",
    "$ROOT\public\css"
)
foreach ($f in $folders) { New-Item -ItemType Directory -Force -Path $f | Out-Null }
Write-Host "   Folders OK"

# ADMIN LAYOUT
Write-Host "[2/5] Creating layouts..."
Set-Content "$ROOT\resources\views\layouts\admin.blade.php" -Encoding UTF8 -Value @'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - Hot Tub Buyer')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
    @yield('styles')
</head>
<body class="panel-body">
    @include('layouts.header')
    <div class="panel-wrapper">
        @include('components.admin-sidebar')
        <div class="panel-main">
            <div class="panel-content">
                @yield('content')
            </div>
        </div>
    </div>
    @yield('scripts')
    <script>
        document.querySelectorAll('.panel-nav-link').forEach(function(link) {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>
'@

Set-Content "$ROOT\resources\views\layouts\dealer.blade.php" -Encoding UTF8 -Value @'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dealer Panel - Hot Tub Buyer')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
    @yield('styles')
</head>
<body class="panel-body">
    @include('layouts.header')
    <div class="panel-wrapper">
        @include('components.dealer-sidebar')
        <div class="panel-main">
            <div class="panel-content">
                @yield('content')
            </div>
        </div>
    </div>
    @yield('scripts')
    <script>
        document.querySelectorAll('.panel-nav-link').forEach(function(link) {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>
'@
Write-Host "   Layouts OK"

# SIDEBARS
Write-Host "[3/5] Creating sidebars..."
Set-Content "$ROOT\resources\views\components\admin-sidebar.blade.php" -Encoding UTF8 -Value @'
<aside class="panel-sidebar">
    <div class="panel-sidebar__head">
        <div class="panel-sidebar__title">Admin Panel</div>
        <div class="panel-sidebar__sub">Manage your platform</div>
    </div>
    <nav class="panel-nav">
        <a href="{{ route('admin.overview') }}" class="panel-nav-link {{ request()->routeIs('admin.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Overview
        </a>
        <a href="{{ route('admin.hot-tubs') }}" class="panel-nav-link {{ request()->routeIs('admin.hot-tubs') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Hot Tubs
        </a>
        <a href="{{ route('admin.brands') }}" class="panel-nav-link {{ request()->routeIs('admin.brands') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Brands
        </a>
        <a href="{{ route('admin.services') }}" class="panel-nav-link {{ request()->routeIs('admin.services') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            Services
        </a>
        <a href="{{ route('admin.parts') }}" class="panel-nav-link {{ request()->routeIs('admin.parts') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            Parts
        </a>
        <a href="{{ route('admin.featured') }}" class="panel-nav-link {{ request()->routeIs('admin.featured') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Featured Content
        </a>
        <a href="{{ route('admin.dealers') }}" class="panel-nav-link {{ request()->routeIs('admin.dealers') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Dealers
        </a>
        <a href="{{ route('admin.manufacturers') }}" class="panel-nav-link {{ request()->routeIs('admin.manufacturers') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Manufacturers
        </a>
        <a href="{{ route('admin.leads') }}" class="panel-nav-link {{ request()->routeIs('admin.leads') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Leads
        </a>
        <a href="{{ route('admin.payments') }}" class="panel-nav-link {{ request()->routeIs('admin.payments') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments
        </a>
    </nav>
</aside>
'@

Set-Content "$ROOT\resources\views\components\dealer-sidebar.blade.php" -Encoding UTF8 -Value @'
<aside class="panel-sidebar">
    <div class="panel-sidebar__head">
        <div class="panel-sidebar__title">Dealer Panel</div>
        <div class="panel-sidebar__sub">Manage your listings</div>
    </div>
    <nav class="panel-nav">
        <a href="{{ route('dealer.overview') }}" class="panel-nav-link {{ request()->routeIs('dealer.overview') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Overview
        </a>
        <a href="{{ route('dealer.leads') }}" class="panel-nav-link {{ request()->routeIs('dealer.leads') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            My Leads
        </a>
        <a href="{{ route('dealer.quotes') }}" class="panel-nav-link {{ request()->routeIs('dealer.quotes') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Quotes
        </a>
        <a href="{{ route('dealer.inventory') }}" class="panel-nav-link {{ request()->routeIs('dealer.inventory') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            My Inventory
        </a>
        <a href="{{ route('dealer.profile') }}" class="panel-nav-link {{ request()->routeIs('dealer.profile') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
        </a>
        <a href="{{ route('dealer.payments') }}" class="panel-nav-link {{ request()->routeIs('dealer.payments') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments
        </a>
    </nav>
</aside>
'@
Write-Host "   Sidebars OK"

# ADMIN + DEALER VIEWS
Write-Host "[4/5] Creating view files..."

Set-Content "$ROOT\resources\views\admin\overview.blade.php" -Encoding UTF8 -Value @'
@extends('layouts.admin')
@section('title', 'Overview - Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Dashboard Overview</h1>
        <p class="panel-page-sub">Platform performance at a glance</p>
    </div>
</div>
<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#eff6ff;">
            <svg width="22" height="22" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div class="panel-stat-card__label">Total Hot Tubs</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#f0fdf4;">
            <svg width="22" height="22" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="panel-stat-card__label">Active Dealers</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#faf5ff;">
            <svg width="22" height="22" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
        </div>
        <div class="panel-stat-card__label">New Leads</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fffbeb;">
            <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/></svg>
        </div>
        <div class="panel-stat-card__label">Conversion Rate</div>
        <div class="panel-stat-card__value">0.0%</div>
    </div>
</div>
<div class="panel-coming-soon">
    <div class="panel-coming-soon__icon">[ Dashboard ]</div>
    <h2>Full Dashboard Coming Soon</h2>
    <p>Charts, recent activity, quick actions and more will be added here.</p>
</div>
@endsection
'@

# Admin simple pages array
$adminData = @(
    @("hot-tubs",      "Hot Tubs",        "Manage all hot tub listings",          "Hot Tubs Manager Comsing Soon",         "Add, edit and manage all hot tub listings, pricing and images."),
    @("brands",        "Brands",          "Manage hot tub brands",                "Brand Manager Coming Soon",            "Add and manage brand profiles, descriptions and logos."),
    @("services",      "Services",        "Manage service listings",              "Services Manager Coming Soon",         "Add and manage installation, maintenance and repair services."),
    @("parts",         "Parts",           "Manage parts catalogue",               "Parts Manager Coming Soon",            "Add and manage replacement parts, pricing and stock levels."),
    @("featured",      "Featured Content","Manage homepage featured listings",    "Featured Content Manager Coming Soon", "Control which hot tubs and brands appear featured on the homepage."),
    @("dealers",       "Dealers",         "Manage dealer accounts and approvals", "Dealers Manager Coming Soon",          "Approve new dealers, manage accounts and view dealer performance."),
    @("manufacturers", "Manufacturers",   "Manage manufacturer accounts",         "Manufacturers Manager Coming Soon",    "Manage manufacturer profiles and their product listings."),
    @("leads",         "Leads",           "View all enquiry leads",               "Leads Manager Coming Soon",            "View and manage all quote requests and service enquiries."),
    @("payments",      "Payments",        "View payment history and billing",     "Payments Manager Coming Soon",         "View subscription payments, invoices and billing history.")
)

foreach ($row in $adminData) {
    $slug  = $row[0]
    $title = $row[1]
    $sub   = $row[2]
    $cs    = $row[3]
    $desc  = $row[4]

    $fileContent  = "@extends('layouts.admin')" + [System.Environment]::NewLine
    $fileContent += "@section('title', '" + $title + " - Admin Panel')" + [System.Environment]::NewLine
    $fileContent += "@section('content')" + [System.Environment]::NewLine
    $fileContent += "<div class=""panel-page-header""><div>" + [System.Environment]::NewLine
    $fileContent += "    <h1 class=""panel-page-title"">" + $title + "</h1>" + [System.Environment]::NewLine
    $fileContent += "    <p class=""panel-page-sub"">" + $sub + "</p>" + [System.Environment]::NewLine
    $fileContent += "</div></div>" + [System.Environment]::NewLine
    $fileContent += "<div class=""panel-coming-soon"">" + [System.Environment]::NewLine
    $fileContent += "    <div class=""panel-coming-soon__icon"">[ " + $title + " ]</div>" + [System.Environment]::NewLine
    $fileContent += "    <h2>" + $cs + "</h2>" + [System.Environment]::NewLine
    $fileContent += "    <p>" + $desc + "</p>" + [System.Environment]::NewLine
    $fileContent += "</div>" + [System.Environment]::NewLine
    $fileContent += "@endsection"

    Set-Content "$ROOT\resources\views\admin\$slug.blade.php" -Encoding UTF8 -Value $fileContent
}

# Dealer overview
Set-Content "$ROOT\resources\views\dealer\overview.blade.php" -Encoding UTF8 -Value @'
@extends('layouts.dealer')
@section('title', 'Overview - Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Overview</h1>
        <p class="panel-page-sub">Your dealer dashboard at a glance</p>
    </div>
</div>
<div class="panel-stats-grid">
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#eff6ff;"><svg width="22" height="22" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div>
        <div class="panel-stat-card__label">New Leads</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#f0fdf4;"><svg width="22" height="22" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <div class="panel-stat-card__label">Quotes Sent</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#faf5ff;"><svg width="22" height="22" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="panel-stat-card__label">In Stock</div>
        <div class="panel-stat-card__value">0</div>
    </div>
    <div class="panel-stat-card">
        <div class="panel-stat-card__icon" style="background:#fffbeb;"><svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/></svg></div>
        <div class="panel-stat-card__label">Conversion</div>
        <div class="panel-stat-card__value">0%</div>
    </div>
</div>
<div class="panel-coming-soon">
    <div class="panel-coming-soon__icon">[ Dashboard ]</div>
    <h2>Full Dashboard Coming Soon</h2>
    <p>Recent leads, quote activity and performance charts will appear here.</p>
</div>
@endsection
'@

# Dealer simple pages
$dealerData = @(
    @("leads",     "My Leads",     "Quote requests sent to your dealership", "Leads Manager Coming Soon",     "All customer quote requests directed to your dealership."),
    @("quotes",    "Quotes",       "Manage your quote responses",            "Quotes Manager Coming Soon",    "Send, track and manage quote responses to potential customers."),
    @("inventory", "My Inventory", "Hot tubs and swim spas in your stock",   "Inventory Manager Coming Soon", "List the hot tubs and swim spas you have available in stock."),
    @("profile",   "My Profile",   "Manage your dealership details",         "Profile Manager Coming Soon",   "Update your dealership name, location, contact details and logo."),
    @("payments",  "Payments",     "Your billing and subscription history",  "Payments Coming Soon",          "View your subscription plan, lead purchase history and invoices.")
)

foreach ($row in $dealerData) {
    $slug  = $row[0]
    $title = $row[1]
    $sub   = $row[2]
    $cs    = $row[3]
    $desc  = $row[4]

    $fileContent  = "@extends('layouts.dealer')" + [System.Environment]::NewLine
    $fileContent += "@section('title', '" + $title + " - Dealer Panel')" + [System.Environment]::NewLine
    $fileContent += "@section('content')" + [System.Environment]::NewLine
    $fileContent += "<div class=""panel-page-header""><div>" + [System.Environment]::NewLine
    $fileContent += "    <h1 class=""panel-page-title"">" + $title + "</h1>" + [System.Environment]::NewLine
    $fileContent += "    <p class=""panel-page-sub"">" + $sub + "</p>" + [System.Environment]::NewLine
    $fileContent += "</div></div>" + [System.Environment]::NewLine
    $fileContent += "<div class=""panel-coming-soon"">" + [System.Environment]::NewLine
    $fileContent += "    <div class=""panel-coming-soon__icon"">[ " + $title + " ]</div>" + [System.Environment]::NewLine
    $fileContent += "    <h2>" + $cs + "</h2>" + [System.Environment]::NewLine
    $fileContent += "    <p>" + $desc + "</p>" + [System.Environment]::NewLine
    $fileContent += "</div>" + [System.Environment]::NewLine
    $fileContent += "@endsection"

    Set-Content "$ROOT\resources\views\dealer\$slug.blade.php" -Encoding UTF8 -Value $fileContent
}

Write-Host "   View files OK"

# CONTROLLERS + ROUTES + CSS
Write-Host "[5/5] Creating Controllers, Routes, CSS..."

Set-Content "$ROOT\app\Http\Controllers\Admin\AdminController.php" -Encoding UTF8 -Value @'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function overview()      { return view('admin.overview'); }
    public function hotTubs()       { return view('admin.hot-tubs'); }
    public function brands()        { return view('admin.brands'); }
    public function services()      { return view('admin.services'); }
    public function parts()         { return view('admin.parts'); }
    public function featured()      { return view('admin.featured'); }
    public function dealers()       { return view('admin.dealers'); }
    public function manufacturers() { return view('admin.manufacturers'); }
    public function leads()         { return view('admin.leads'); }
    public function payments()      { return view('admin.payments'); }
}
'@

Set-Content "$ROOT\app\Http\Controllers\Dealer\DealerController.php" -Encoding UTF8 -Value @'
<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;

class DealerController extends Controller
{
    public function overview()  { return view('dealer.overview'); }
    public function leads()     { return view('dealer.leads'); }
    public function quotes()    { return view('dealer.quotes'); }
    public function inventory() { return view('dealer.inventory'); }
    public function profile()   { return view('dealer.profile'); }
    public function payments()  { return view('dealer.payments'); }
}
'@

Set-Content "$ROOT\routes\web.php" -Encoding UTF8 -Value @'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Dealer\DealerController;

// PUBLIC PAGES
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/hot-tubs',    [PageController::class, 'hotTubs'])->name('hot-tubs');
Route::get('/swim-spas',   [PageController::class, 'swimSpas'])->name('swim-spas');
Route::get('/services',    [PageController::class, 'services'])->name('services');
Route::get('/parts',       [PageController::class, 'parts'])->name('parts');
Route::get('/brands',      [PageController::class, 'brands'])->name('brands');
Route::get('/find-dealer', [PageController::class, 'findDealer'])->name('find-dealer');
Route::get('/care-guide',  [PageController::class, 'careGuide'])->name('care-guide');
Route::get('/faq',         [PageController::class, 'faq'])->name('faq');

// AUTH PAGES
Route::get('/login',    [PageController::class, 'login'])->name('login');
Route::get('/register', [PageController::class, 'register'])->name('register');

// ADMIN PANEL
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminController::class, 'overview'])->name('overview');
    Route::get('/hot-tubs',      [AdminController::class, 'hotTubs'])->name('hot-tubs');
    Route::get('/brands',        [AdminController::class, 'brands'])->name('brands');
    Route::get('/services',      [AdminController::class, 'services'])->name('services');
    Route::get('/parts',         [AdminController::class, 'parts'])->name('parts');
    Route::get('/featured',      [AdminController::class, 'featured'])->name('featured');
    Route::get('/dealers',       [AdminController::class, 'dealers'])->name('dealers');
    Route::get('/manufacturers', [AdminController::class, 'manufacturers'])->name('manufacturers');
    Route::get('/leads',         [AdminController::class, 'leads'])->name('leads');
    Route::get('/payments',      [AdminController::class, 'payments'])->name('payments');
});

// DEALER PANEL
Route::prefix('dealer')->name('dealer.')->group(function () {
    Route::get('/',          [DealerController::class, 'overview'])->name('overview');
    Route::get('/leads',     [DealerController::class, 'leads'])->name('leads');
    Route::get('/quotes',    [DealerController::class, 'quotes'])->name('quotes');
    Route::get('/inventory', [DealerController::class, 'inventory'])->name('inventory');
    Route::get('/profile',   [DealerController::class, 'profile'])->name('profile');
    Route::get('/payments',  [DealerController::class, 'payments'])->name('payments');
});
'@

Set-Content "$ROOT\public\css\panel.css" -Encoding UTF8 -Value @'
/* PANEL CSS - Admin & Dealer Layout - public/css/panel.css */
.panel-body { background: var(--gray-50); }
.panel-wrapper { display: flex; min-height: calc(100vh - var(--navbar-h)); }
.panel-sidebar { width: 240px; flex-shrink: 0; background: var(--white); border-right: 1px solid var(--gray-200); position: sticky; top: var(--navbar-h); height: calc(100vh - var(--navbar-h)); overflow-y: auto; display: flex; flex-direction: column; z-index: 100; }
.panel-sidebar__head { padding: 1.5rem 1.25rem 1rem; border-bottom: 1px solid var(--gray-100); }
.panel-sidebar__title { font-size: 1rem; font-weight: 800; color: var(--gray-900); margin-bottom: .15rem; }
.panel-sidebar__sub { font-size: .75rem; color: var(--gray-400); }
.panel-nav { padding: .75rem; display: flex; flex-direction: column; gap: .15rem; }
.panel-nav-link { display: flex; align-items: center; gap: .75rem; padding: .65rem .85rem; border-radius: var(--r-md); font-size: .88rem; font-weight: 500; color: var(--gray-700); transition: var(--transition); text-decoration: none; }
.panel-nav-link:hover { background: var(--teal-xlt); color: var(--teal); }
.panel-nav-link.active { background: var(--teal-xlt); color: var(--teal-dk); font-weight: 700; }
.panel-nav-link svg { flex-shrink: 0; color: var(--gray-400); transition: color .2s; }
.panel-nav-link:hover svg, .panel-nav-link.active svg { color: var(--teal); }
.panel-nav-badge { margin-left: auto; background: #ef4444; color: white; font-size: .65rem; font-weight: 800; padding: .15rem .5rem; border-radius: var(--r-pill); }
.panel-main { flex: 1; min-width: 0; }
.panel-content { padding: 2rem; max-width: 1200px; }
.panel-page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.panel-page-title { font-size: 1.6rem; font-weight: 800; color: var(--gray-900); margin-bottom: .2rem; }
.panel-page-sub { font-size: .88rem; color: var(--gray-500); }
.panel-stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.25rem; margin-bottom: 2rem; }
.panel-stat-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--r-lg); padding: 1.5rem; box-shadow: var(--shadow-xs); }
.panel-stat-card__icon { width: 48px; height: 48px; border-radius: var(--r-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
.panel-stat-card__label { font-size: .82rem; color: var(--gray-500); margin-bottom: .3rem; }
.panel-stat-card__value { font-size: 1.75rem; font-weight: 800; color: var(--gray-900); }
.panel-coming-soon { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--r-xl); padding: 4rem 2rem; text-align: center; box-shadow: var(--shadow-xs); }
.panel-coming-soon__icon { font-size: 1.1rem; font-weight: 700; color: var(--teal); margin-bottom: 1rem; background: var(--teal-xlt); display: inline-block; padding: .5rem 1rem; border-radius: var(--r-md); }
.panel-coming-soon h2 { font-size: 1.4rem; font-weight: 800; color: var(--gray-900); margin-bottom: .75rem; }
.panel-coming-soon p { font-size: .93rem; color: var(--gray-500); max-width: 420px; margin: 0 auto; line-height: 1.7; }
@media (max-width: 1100px) { .panel-stats-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 900px) { .panel-sidebar { position: fixed; left: -240px; transition: left .25s ease; z-index: 999; box-shadow: var(--shadow-lg); } .panel-sidebar--open { left: 0; } .panel-content { padding: 1.25rem; } }
@media (max-width: 600px) { .panel-stats-grid { grid-template-columns: 1fr 1fr; } .panel-page-title { font-size: 1.3rem; } }
'@

Write-Host "   Controllers + Routes + CSS OK"

# CLEAR CACHE
Write-Host ""
Write-Host "Clearing Laravel cache..."
Set-Location $ROOT
& php artisan config:clear
& php artisan route:clear
& php artisan view:clear

Write-Host ""
Write-Host "================================================"
Write-Host " ALL DONE! Ab browser mein test karo:"
Write-Host "   http://localhost:8000/admin"
Write-Host "   http://localhost:8000/dealer"
Write-Host "================================================"
pause
