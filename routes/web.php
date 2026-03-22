<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DealerManagementController;
use App\Http\Controllers\Admin\FeaturedContentController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\ManufacturerManagementController;
use App\Http\Controllers\Admin\PartController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentProcessorController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Dealer\DealerController;
use App\Http\Controllers\Manufacturer\ManufacturerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// ══ PUBLIC PAGES ══════════════════════════════════════════════════════════════
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/dealer-agreement', 'pages.dealer-agreement')->name('dealer-agreement');

Route::get('/hot-tubs', [PageController::class, 'hotTubs'])->name('hot-tubs');
Route::get('/hot-tubs/{slug}', [PageController::class, 'hotTubDetail'])->name('hot-tubs.detail');
Route::get('/swim-spas', [PageController::class, 'swimSpas'])->name('swim-spas');
Route::get('/swim-spas/{slug}', [PageController::class, 'swimSpaDetail'])->name('swim-spas.detail');
Route::get('/outdoor-products', [PageController::class, 'outdoorProducts'])->name('outdoor-products');
Route::get('/outdoor-products/{slug}', [PageController::class, 'outdoorProductDetail'])->name('outdoor-products.detail');
Route::get('/brands', [PageController::class, 'brands'])->name('brands');
Route::get('/brands/{slug}', [PageController::class, 'brandDetail'])->name('brands.detail');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/services/{slug}', [PageController::class, 'serviceDetail'])->name('services.detail');
Route::get('/parts', [PageController::class, 'parts'])->name('parts');
Route::get('/parts/{slug}', [PageController::class, 'partDetail'])->name('parts.detail');
Route::get('/find-dealer', [PageController::class, 'findDealer'])->name('find-dealer');
Route::get('/care-guide', [PageController::class, 'careGuide'])->name('care-guide');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');

// ══ AUTH ══════════════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Password Routes
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showEmailForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/verify-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showOtpForm'])->name('password.otp.form');
    Route::post('/verify-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/reset-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.update.new');
    Route::get('/password-success', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showSuccess'])->name('password.success');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public enquiry submit (creates a Lead)
Route::post('/enquiry', [\App\Http\Controllers\EnquiryController::class, 'submit'])->name('enquiry.submit');

// Unified dashboard redirect for any authenticated user
Route::get('/dashboard', function () {
    $u = auth()->user();
    if (!$u)
        return redirect()->route('login');
    return match ($u->role) {
        'admin' => redirect()->route('admin.overview'),
        'dealer' => redirect()->route('dealer.overview'),
        'manufacturer' => redirect()->route('manufacturer.overview'),
        default => redirect()->route('customer.overview'),
    };
})->middleware('auth')->name('dashboard');

// ══ ADMIN PANEL ═══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'overview'])->name('overview');
    // Hot Tubs CRUD
    Route::get('/hot-tubs', [\App\Http\Controllers\Admin\HotTubController::class, 'index'])->name('hot-tubs.index');
    Route::post('/hot-tubs', [\App\Http\Controllers\Admin\HotTubController::class, 'store'])->name('hot-tubs.store');
    Route::get('/hot-tubs/{hot_tub}/edit', [\App\Http\Controllers\Admin\HotTubController::class, 'edit'])->name('hot-tubs.edit');
    Route::put('/hot-tubs/{hot_tub}', [\App\Http\Controllers\Admin\HotTubController::class, 'update'])->name('hot-tubs.update');
    Route::delete('/hot-tubs/{hot_tub}', [\App\Http\Controllers\Admin\HotTubController::class, 'destroy'])->name('hot-tubs.destroy');
    Route::post('/hot-tubs/{hot_tub}/images', [\App\Http\Controllers\Admin\HotTubController::class, 'uploadImages'])->name('hot-tubs.images');
    Route::delete('/hot-tubs/{hot_tub}/images/{index}', [\App\Http\Controllers\Admin\HotTubController::class, 'deleteImage'])->name('hot-tubs.images.delete');
    Route::post('/hot-tubs/{hot_tub}/set-main-image', [\App\Http\Controllers\Admin\HotTubController::class, 'setMainImage'])->name('hot-tubs.set-main-image');

    // Outdoor Products CRUD
    Route::get('/outdoor-products', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'index'])->name('outdoor-products.index');
    Route::post('/outdoor-products', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'store'])->name('outdoor-products.store');
    Route::get('/outdoor-products/{outdoor_product}/edit', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'edit'])->name('outdoor-products.edit');
    Route::put('/outdoor-products/{outdoor_product}', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'update'])->name('outdoor-products.update');
    Route::delete('/outdoor-products/{outdoor_product}', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'destroy'])->name('outdoor-products.destroy');
    Route::post('/outdoor-products/{outdoor_product}/images', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'uploadImages'])->name('outdoor-products.images');
    Route::delete('/outdoor-products/{outdoor_product}/images/{index}', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'deleteImage'])->name('outdoor-products.images.delete');
    Route::post('/outdoor-products/{outdoor_product}/set-main-image', [\App\Http\Controllers\Admin\OutdoorProductController::class, 'setMainImage'])->name('outdoor-products.set-main-image');
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::get('/parts', [PartController::class, 'index'])->name('parts');
    Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
    Route::get('/parts/{part}/edit', [PartController::class, 'edit'])->name('parts.edit');
    Route::put('/parts/{part}', [PartController::class, 'update'])->name('parts.update');
    Route::delete('/parts/{part}', [PartController::class, 'destroy'])->name('parts.destroy');
    Route::get('/featured', [FeaturedContentController::class, 'index'])->name('featured');
    Route::post('/featured', [FeaturedContentController::class, 'store'])->name('featured.store');
    Route::get('/featured/{featured}/edit', [FeaturedContentController::class, 'edit'])->name('featured.edit');
    Route::put('/featured/{featured}', [FeaturedContentController::class, 'update'])->name('featured.update');
    Route::delete('/featured/{featured}', [FeaturedContentController::class, 'destroy'])->name('featured.destroy');
    Route::get('/manufacturers', [ManufacturerManagementController::class, 'index'])->name('manufacturers');
    Route::prefix('manufacturers')->name('manufacturers.')->group(function () {
        Route::post('/', [ManufacturerManagementController::class, 'store'])->name('store');
        Route::get('/{manufacturer}/edit', [ManufacturerManagementController::class, 'edit'])->name('edit');
        Route::put('/{manufacturer}', [ManufacturerManagementController::class, 'update'])->name('update');
        Route::delete('/{manufacturer}', [ManufacturerManagementController::class, 'destroy'])->name('destroy');
        Route::patch('/{manufacturer}/approve', [ManufacturerManagementController::class, 'approve'])->name('approve');
        Route::patch('/{manufacturer}/revoke', [ManufacturerManagementController::class, 'revoke'])->name('revoke');
        Route::get('/{manufacturer}/credits', [ManufacturerManagementController::class, 'credits'])->name('credits');
        Route::post('/{manufacturer}/credits', [ManufacturerManagementController::class, 'addCredits'])->name('credits.add');
    });
    // Service Management
    Route::get('/service-management', [ServiceManagementController::class, 'index'])->name('service-management');
    Route::get('/service-management/{serviceRequest}/download', [ServiceManagementController::class, 'downloadReport'])->name('service-management.download');

    // Leads
    Route::get('/leads', [LeadController::class, 'index'])->name('leads');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::get('/leads/{lead}/activity', [LeadController::class, 'activity'])->name('leads.activity');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/credits/{request}/approve', [AdminController::class, 'approveCreditRequest'])->name('credits.approve');
    Route::post('/credits/{request}/reject', [AdminController::class, 'rejectCreditRequest'])->name('credits.reject');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{invoice}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{invoice}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{invoice}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/pricing-processor', [PaymentProcessorController::class, 'index'])->name('pricing-processor');
    Route::post('/pricing-processor', [PaymentProcessorController::class, 'save'])->name('pricing-processor.save');
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
    Route::post('/pricing/packages', [PricingController::class, 'savePackages'])->name('pricing.packages');
    Route::post('/pricing/enquiry', [PricingController::class, 'saveEnquiryPricing'])->name('pricing.enquiry');
    Route::post('/pricing/leads', [PricingController::class, 'saveLeadPricing'])->name('pricing.leads');
    Route::post('/pricing/featured', [PricingController::class, 'saveFeaturedPricing'])->name('pricing.featured');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

    // ── Dealer Management ──────────────────────────────────────────────────
    Route::prefix('dealers')->name('dealers.')->group(function () {
        Route::get('/', [DealerManagementController::class, 'index'])->name('index');
        Route::get('/create', [DealerManagementController::class, 'create'])->name('create');
        Route::post('/', [DealerManagementController::class, 'store'])->name('store');
        Route::get('/{dealer}/edit', [DealerManagementController::class, 'edit'])->name('edit');
        Route::put('/{dealer}', [DealerManagementController::class, 'update'])->name('update');
        Route::delete('/{dealer}', [DealerManagementController::class, 'destroy'])->name('destroy');
        Route::patch('/{dealer}/approve', [DealerManagementController::class, 'approve'])->name('approve');
        Route::patch('/{dealer}/revoke', [DealerManagementController::class, 'revoke'])->name('revoke');
        Route::get('/{dealer}/credits', [DealerManagementController::class, 'credits'])->name('credits');
        Route::post('/{dealer}/credits', [DealerManagementController::class, 'addCredits'])->name('credits.add');
        Route::get('/{dealer}/reset-password', [DealerManagementController::class, 'resetPassword'])->name('reset-password');
    });

    // ── Dealer Academy ─────────────────────────────────────────────────────
    Route::resource('dealer-academy', \App\Http\Controllers\Admin\DealerAcademyController::class);

    // ── User Management ──────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('index');
        Route::put('/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('update');
    });

    // ── Support Requests ──────────────────────────────────────────────────
    Route::get('/support-requests', [\App\Http\Controllers\Admin\AdminController::class, 'supportRequests'])->name('support-requests');
});

// ══ DEALER PANEL ══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'dealer'])->prefix('dealer')->name('dealer.')->group(function () {
    Route::get('/', [DealerController::class, 'overview'])->name('overview');
    Route::get('/leads', [DealerController::class, 'leads'])->name('leads.index');
    Route::post('/leads/private', [DealerController::class, 'storePrivateLead'])->name('leads.private.store');
    
    // Maintenance Packages
    Route::get('/maintenance-packages', [DealerController::class, 'maintenancePackages'])->name('maintenance-packages');
    Route::post('/maintenance-packages', [DealerController::class, 'storeMaintenancePackage'])->name('maintenance-packages.store');
    Route::get('/maintenance-packages/{package}/edit', [DealerController::class, 'editMaintenancePackage'])->name('maintenance-packages.edit');
    Route::put('/maintenance-packages/{package}', [DealerController::class, 'updateMaintenancePackage'])->name('maintenance-packages.update');
    Route::delete('/maintenance-packages/{package}', [DealerController::class, 'destroyMaintenancePackage'])->name('maintenance-packages.destroy');
    
    Route::get('/package-requests', [DealerController::class, 'packageRequests'])->name('package-requests');
    Route::put('/package-requests/{packageRequest}', [DealerController::class, 'updatePackageRequestStatus'])->name('package-requests.update');
    Route::post('/leads/{lead}/buy', [DealerController::class, 'buyLead'])->name('leads.buy');
    Route::get('/leads/{lead}/view', [DealerController::class, 'viewLead'])->name('leads.view');
    Route::get('/leads/{lead}/guidance/download', [DealerController::class, 'downloadGuidance'])->name('leads.guidance.download');
    Route::get('/leads/{lead}', [DealerController::class, 'leadDetail'])->name('leads.detail');
    Route::post('/leads/{lead}/stage', [DealerController::class, 'updateLeadStage'])->name('leads.stage');
    Route::post('/leads/{lead}/activity', [DealerController::class, 'addLeadActivity'])->name('leads.activity');
    Route::post('/activities/{activity}/toggle', [DealerController::class, 'toggleTask'])->name('activities.toggle');
    Route::post('/leads/{lead}/deliver', [DealerController::class, 'deliverLead'])->name('leads.deliver');

    // Dealer Service System
    Route::post('/leads/{lead}/service-checklist', [DealerController::class, 'storeServiceChecklist'])->name('leads.service-checklist');
    Route::get('/leads/{lead}/service-history', [DealerController::class, 'getServiceHistory'])->name('leads.service-history');
    Route::get('/service-history', [DealerController::class, 'serviceHistory'])->name('service-history');
    Route::get('/service-requests', [DealerController::class, 'serviceRequests'])->name('service-requests');
    Route::put('/service-requests/{serviceRequest}', [DealerController::class, 'updateServiceRequestStatus'])->name('service-requests.update');
    Route::get('/quotes', [DealerController::class, 'quotes'])->name('quotes');
    Route::get('/inventory', [DealerController::class, 'inventory'])->name('inventory');
    Route::get('/credits', [DealerController::class, 'credits'])->name('credits');
    Route::post('/credits/request', [DealerController::class, 'requestCredits'])->name('credits.request');
    Route::get('/profile', [DealerController::class, 'profile'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/payments', [DealerController::class, 'payments'])->name('payments');
    Route::get('/invoices/{invoice}', [DealerController::class, 'invoice'])->name('invoice');
    Route::get('/invoices/{invoice}/download', [DealerController::class, 'invoiceDownload'])->name('invoice.download');
    
    // Dealer Academy
    Route::get('/academy', [\App\Http\Controllers\Dealer\DealerAcademyController::class, 'index'])->name('academy.index');
    Route::get('/academy/{dealer_academy}', [\App\Http\Controllers\Dealer\DealerAcademyController::class, 'show'])->name('academy.show');
    
    Route::get('/messages', function() { return view('dealer.messages'); })->name('messages');
    Route::get('/api/conversations', [MessageController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/messages/{user}', [MessageController::class, 'getMessages'])->name('api.messages');
    Route::post('/api/messages/{user}', [MessageController::class, 'sendMessage'])->name('api.send_message');
});

// ══ MANUFACTURER PANEL ════════════════════════════════════════════════════════
Route::middleware(['auth', 'manufacturer'])->prefix('manufacturer')->name('manufacturer.')->group(function () {
    Route::get('/', [ManufacturerController::class, 'overview'])->name('overview');
    Route::get('/leads', [ManufacturerController::class, 'leads'])->name('leads');
    Route::post('/leads/private', [ManufacturerController::class, 'storePrivateLead'])->name('leads.private.store');
    Route::post('/leads/{lead}/buy', [ManufacturerController::class, 'buyLead'])->name('leads.buy');
    Route::get('/leads/{lead}/view', [ManufacturerController::class, 'viewLead'])->name('leads.view');
    Route::get('/leads/{lead}/guidance/download', [ManufacturerController::class, 'downloadGuidance'])->name('leads.guidance.download');
    Route::get('/leads/{lead}', [ManufacturerController::class, 'leadDetail'])->name('leads.detail');
    Route::post('/leads/{lead}/stage', [ManufacturerController::class, 'updateLeadStage'])->name('leads.stage');
    Route::post('/leads/{lead}/activity', [ManufacturerController::class, 'addLeadActivity'])->name('leads.activity');
    Route::post('/activities/{activity}/toggle', [ManufacturerController::class, 'toggleTask'])->name('activities.toggle');
    Route::post('/leads/{lead}/deliver', [ManufacturerController::class, 'deliverLead'])->name('leads.deliver');
    Route::get('/quotes', [ManufacturerController::class, 'quotes'])->name('quotes');
    Route::get('/inventory', [ManufacturerController::class, 'inventory'])->name('inventory');
    Route::get('/credits', [ManufacturerController::class, 'credits'])->name('credits');
    Route::post('/credits/request', [ManufacturerController::class, 'requestCredits'])->name('credits.request');
    Route::get('/profile', [ManufacturerController::class, 'profile'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/payments', [ManufacturerController::class, 'payments'])->name('payments');
    Route::get('/invoices/{invoice}', [ManufacturerController::class, 'invoice'])->name('invoice');
    Route::get('/invoices/{invoice}/download', [ManufacturerController::class, 'invoiceDownload'])->name('invoice.download');

    // Maintenance Packages
    Route::get('/maintenance-packages', [ManufacturerController::class, 'maintenancePackages'])->name('maintenance-packages');
    Route::post('/maintenance-packages', [ManufacturerController::class, 'storeMaintenancePackage'])->name('maintenance-packages.store');
    Route::get('/maintenance-packages/{package}/edit', [ManufacturerController::class, 'editMaintenancePackage'])->name('maintenance-packages.edit');
    Route::put('/maintenance-packages/{package}', [ManufacturerController::class, 'updateMaintenancePackage'])->name('maintenance-packages.update');
    Route::delete('/maintenance-packages/{package}', [ManufacturerController::class, 'destroyMaintenancePackage'])->name('maintenance-packages.destroy');
    
    Route::get('/package-requests', [ManufacturerController::class, 'packageRequests'])->name('package-requests');
    Route::put('/package-requests/{packageRequest}', [ManufacturerController::class, 'updatePackageRequestStatus'])->name('package-requests.update');

    // Service Requests
    Route::get('/service-requests', [ManufacturerController::class, 'serviceRequests'])->name('service-requests');
    Route::get('/service-history', [ManufacturerController::class, 'serviceHistory'])->name('service-history');
    Route::put('/service-requests/{serviceRequest}', [ManufacturerController::class, 'updateServiceRequestStatus'])->name('service-requests.update');
    
    Route::get('/messages', function() { return view('manufacturer.messages'); })->name('messages');
    Route::get('/api/conversations', [MessageController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/messages/{user}', [MessageController::class, 'getMessages'])->name('api.messages');
    Route::post('/api/messages/{user}', [MessageController::class, 'sendMessage'])->name('api.send_message');
});

// ══ CUSTOMER PANEL ════════════════════════════════════════════════════════════
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Customer\CustomerController::class, 'overview'])->name('overview');
    Route::get('/hot-tub', [\App\Http\Controllers\Customer\CustomerController::class, 'myHotTub'])->name('hot-tub');
    Route::get('/service-requests', [\App\Http\Controllers\Customer\CustomerController::class, 'serviceRequests'])->name('service-requests');
    Route::get('/request-history', [\App\Http\Controllers\Customer\CustomerController::class, 'requestHistory'])->name('request-history');

    // Customer Service System
    Route::get('/service-history', [\App\Http\Controllers\Customer\CustomerController::class, 'serviceHistory'])->name('service-history');
    Route::post('/service-history/{checklist}/sign', [\App\Http\Controllers\Customer\CustomerController::class, 'signService'])->name('service.sign');
    Route::post('/service-requests', [\App\Http\Controllers\Customer\CustomerController::class, 'storeServiceRequest'])->name('service-requests.store');
    Route::put('/service-requests/{serviceRequest}/confirm', [\App\Http\Controllers\Customer\CustomerController::class, 'confirmServiceRequest'])->name('service-requests.confirm');
    Route::post('/package-requests', [\App\Http\Controllers\Customer\CustomerController::class, 'storePackageRequest'])->name('package-requests.store');
    Route::get('/messages', [\App\Http\Controllers\Customer\CustomerController::class, 'messages'])->name('messages');

    // API-like routes for messaging
    Route::get('/api/conversations', [\App\Http\Controllers\MessageController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/messages/{user}', [\App\Http\Controllers\MessageController::class, 'getMessages'])->name('api.messages');
    Route::post('/api/messages/{user}', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->name('api.send_message');
    Route::get('/profile', [\App\Http\Controllers\Customer\CustomerController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\Customer\CustomerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/image', [\App\Http\Controllers\Customer\CustomerController::class, 'updateProfileImage'])->name('profile.update-image');
});
