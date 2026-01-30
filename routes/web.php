<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Admin\TenantProductsController;
use App\Http\Controllers\Admin\TenantCustomersController;
use App\Http\Controllers\Admin\TenantOrdersController;
use App\Http\Controllers\Admin\TenantCategoriesController;
use App\Http\Controllers\Admin\TenantAttributesController;
use App\Http\Controllers\Admin\TenantAttributeFamiliesController;
use App\Http\Controllers\Auth\MerchantRegisterController;
use App\Http\Controllers\Merchant\DomainVerificationController;
use App\Http\Controllers\Merchant\MerchantAuthController;
use App\Http\Controllers\Merchant\MerchantDashboardController;
use App\Http\Controllers\Merchant\MerchantSettingsController;
use App\Http\Controllers\ProvisioningController;
use Illuminate\Support\Facades\Route;

// Debug routes - MUST be before tenant.resolve middleware
Route::get('/__tenant_debug', function () {
    try {
        $host = request()->getHost();
        $resolver = app(\App\Services\Tenant\TenantResolver::class);
        $resolved = $resolver->resolveByHost($host);
        
        $domains = \App\Models\Tenant\Domain::all(['id', 'domain', 'type', 'verified_at', 'tenant_id']);
        $tenants = \App\Models\Tenant\Tenant::all(['id', 'name', 'slug', 'status']);
        
        return response()->json([
            'host' => $host,
            'normalized_host' => $resolver->normalizeHost($host),
            'resolved' => $resolved ? [
                'tenant_id' => $resolved['tenant']->id,
                'tenant_name' => $resolved['tenant']->name,
                'tenant_status' => $resolved['tenant']->status,
                'domain' => $resolved['domain']->domain,
                'domain_type' => $resolved['domain']->type,
                'domain_verified_at' => $resolved['domain']->verified_at?->toDateTimeString(),
            ] : null,
            'all_domains' => $domains->map(fn($d) => [
                'id' => $d->id,
                'domain' => $d->domain,
                'type' => $d->type,
                'verified_at' => $d->verified_at?->toDateTimeString(),
                'tenant_id' => $d->tenant_id,
            ])->toArray(),
            'all_tenants' => $tenants->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'status' => $t->status,
            ])->toArray(),
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->withoutMiddleware(['tenant.resolve', 'web']);

if (app()->environment(['local', 'testing'])) {
    Route::middleware(['tenant.resolve'])->get('/__tenant_ping', function () {
        return response('ok');
    });
}

Route::get('/register', [MerchantRegisterController::class, 'showStep1'])->name('merchant.register.step1');
Route::post('/register/step-1', [MerchantRegisterController::class, 'postStep1'])->name('merchant.register.postStep1');

Route::get('/register/personal', [MerchantRegisterController::class, 'showStep2'])->name('merchant.register.step2');
Route::post('/register/step-2', [MerchantRegisterController::class, 'postStep2'])->name('merchant.register.postStep2');

Route::get('/register/organization', [MerchantRegisterController::class, 'showStep3'])->name('merchant.register.step3');
Route::post('/register/complete', [MerchantRegisterController::class, 'complete'])->name('merchant.register.complete');

Route::get('/provisioning/{tenant}/progress', [ProvisioningController::class, 'progress'])
    ->name('provisioning.progress');

Route::get('/provisioning/{tenant}/status', [ProvisioningController::class, 'status'])
    ->name('provisioning.status');

// Patch-12 merchant domain verification endpoints (wizard/session-based)
Route::prefix('merchant')->middleware(['merchant.onboarding'])->group(function () {
    Route::post('domains', [DomainVerificationController::class, 'store'])->name('merchant.domains.store');
    Route::get('domains/{domain}/instructions', [DomainVerificationController::class, 'instructions'])->name('merchant.domains.instructions');
    Route::get('domains/{domain}/status', [DomainVerificationController::class, 'status'])->name('merchant.domains.status');
    Route::post('domains/{domain}/verify', [DomainVerificationController::class, 'verify'])->name('merchant.domains.verify');
    Route::post('domains/{domain}/rotate-token', [DomainVerificationController::class, 'rotateToken'])->name('merchant.domains.rotate');
});

// Patch-13 merchant portal (auth:merchant)
Route::prefix('merchant')->group(function () {
    Route::get('login', [MerchantAuthController::class, 'showLogin'])->name('merchant.login');
    Route::post('login', [MerchantAuthController::class, 'login'])->name('merchant.login.post');

    Route::middleware(['auth:merchant'])->group(function () {
        Route::post('logout', [MerchantAuthController::class, 'logout'])->name('merchant.logout');

        Route::get('', [MerchantDashboardController::class, 'index'])->name('merchant.dashboard');

        Route::post('domains/add', [MerchantDashboardController::class, 'addDomain'])->name('merchant.portal.domains.add');
        Route::post('domains/{domain}/verify-now', [MerchantDashboardController::class, 'verifyNow'])->name('merchant.portal.domains.verify');
        Route::post('domains/{domain}/rotate-token', [MerchantDashboardController::class, 'rotateToken'])->name('merchant.portal.domains.rotate');

        Route::get('settings', [MerchantSettingsController::class, 'edit'])->name('merchant.settings.edit');
        Route::post('settings', [MerchantSettingsController::class, 'update'])->name('merchant.settings.update');
    });
});

// Patch-13 super admin tenant ops (auth:admin)
Route::prefix(config('app.admin_url') . '/tenants')->middleware(['web', 'auth:admin'])->group(function () {
    Route::get('', [TenantManagementController::class, 'index'])->name('admin.tenants.index');
    Route::get('create', [TenantManagementController::class, 'create'])->name('admin.tenants.create');
    Route::post('', [TenantManagementController::class, 'store'])->name('admin.tenants.store');
    Route::get('{tenant}', [TenantManagementController::class, 'show'])->name('admin.tenants.show');
    Route::post('{tenant}/retry-provisioning', [TenantManagementController::class, 'retryProvisioning'])->name('admin.tenants.retry');
    Route::post('{tenant}/toggle-status', [TenantManagementController::class, 'toggleStatus'])->name('admin.tenants.toggle');
    
    // Tenant store views
    Route::get('{tenant}/products', [TenantProductsController::class, 'index'])->name('admin.tenants.products.index');
    Route::get('{tenant}/products/{product}', [TenantProductsController::class, 'show'])->name('admin.tenants.products.show');
    
    // Tenant customers
    Route::get('{tenant}/customers', [TenantCustomersController::class, 'index'])->name('admin.tenants.customers.index');
    Route::get('{tenant}/customers/{customer}', [TenantCustomersController::class, 'show'])->name('admin.tenants.customers.show');
    Route::post('{tenant}/customers/{customer}/suspend', [TenantCustomersController::class, 'suspend'])->name('admin.tenants.customers.suspend');
    Route::post('{tenant}/customers/{customer}/activate', [TenantCustomersController::class, 'activate'])->name('admin.tenants.customers.activate');
    
    // Tenant catalog
    Route::get('{tenant}/categories', [TenantCategoriesController::class, 'index'])->name('admin.tenants.categories.index');
    Route::get('{tenant}/attributes', [TenantAttributesController::class, 'index'])->name('admin.tenants.attributes.index');
    Route::get('{tenant}/attribute-families', [TenantAttributeFamiliesController::class, 'index'])->name('admin.tenants.attribute-families.index');
    
    Route::get('{tenant}/orders', [TenantOrdersController::class, 'index'])->name('admin.tenants.orders.index');
    Route::get('{tenant}/orders/{order}', [TenantOrdersController::class, 'show'])->name('admin.tenants.orders.show');
});

Route::prefix(config('app.admin_url') . '/domains')->middleware(['web', 'auth:admin'])->group(function () {
    Route::post('{domain}/rotate-token', [TenantManagementController::class, 'rotateDomainToken'])->name('admin.domains.rotate');
    Route::post('{domain}/verify', [TenantManagementController::class, 'verifyDomain'])->name('admin.domains.verify');
});
