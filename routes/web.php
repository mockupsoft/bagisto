<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Auth\MerchantRegisterController;
use App\Http\Controllers\Merchant\DomainVerificationController;
use App\Http\Controllers\Merchant\MerchantAuthController;
use App\Http\Controllers\Merchant\MerchantDashboardController;
use App\Http\Controllers\Merchant\MerchantSettingsController;
use App\Http\Controllers\ProvisioningController;
use Illuminate\Support\Facades\Route;

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
Route::prefix('merchant')->group(function () {
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

        Route::post('domains/add', [MerchantDashboardController::class, 'addDomain'])->name('merchant.domains.add');
        Route::post('domains/{domain}/verify-now', [MerchantDashboardController::class, 'verifyNow'])->name('merchant.domains.verify');
        Route::post('domains/{domain}/rotate-token', [MerchantDashboardController::class, 'rotateToken'])->name('merchant.domains.rotate');

        Route::get('settings', [MerchantSettingsController::class, 'edit'])->name('merchant.settings.edit');
        Route::post('settings', [MerchantSettingsController::class, 'update'])->name('merchant.settings.update');
    });
});

// Patch-13 super admin tenant ops (auth:admin)
Route::prefix(config('app.admin_url') . '/tenants')->middleware(['web', 'auth:admin'])->group(function () {
    Route::get('', [TenantManagementController::class, 'index'])->name('admin.tenants.index');
    Route::get('{tenant}', [TenantManagementController::class, 'show'])->name('admin.tenants.show');
    Route::post('{tenant}/retry-provisioning', [TenantManagementController::class, 'retryProvisioning'])->name('admin.tenants.retry');
    Route::post('{tenant}/toggle-status', [TenantManagementController::class, 'toggleStatus'])->name('admin.tenants.toggle');
});

Route::prefix(config('app.admin_url') . '/domains')->middleware(['web', 'auth:admin'])->group(function () {
    Route::post('{domain}/rotate-token', [TenantManagementController::class, 'rotateDomainToken'])->name('admin.domains.rotate');
    Route::post('{domain}/verify', [TenantManagementController::class, 'verifyDomain'])->name('admin.domains.verify');
});
