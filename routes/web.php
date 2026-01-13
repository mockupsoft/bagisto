<?php

use App\Http\Controllers\Auth\MerchantRegisterController;
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

Route::get('/provisioning', function () {
    return view('provisioning.stub');
})->name('merchant.provisioning.stub');
