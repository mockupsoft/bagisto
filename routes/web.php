<?php

use App\Http\Controllers\Auth\MerchantRegisterController;
use App\Http\Controllers\Merchant\DomainVerificationController;
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

Route::prefix('merchant')->group(function () {
    Route::post('domains', [DomainVerificationController::class, 'store'])->name('merchant.domains.store');
    Route::get('domains/{domain}/instructions', [DomainVerificationController::class, 'instructions'])->name('merchant.domains.instructions');
    Route::get('domains/{domain}/status', [DomainVerificationController::class, 'status'])->name('merchant.domains.status');
    Route::post('domains/{domain}/verify', [DomainVerificationController::class, 'verify'])->name('merchant.domains.verify');
    Route::post('domains/{domain}/rotate-token', [DomainVerificationController::class, 'rotateToken'])->name('merchant.domains.rotate');
});
