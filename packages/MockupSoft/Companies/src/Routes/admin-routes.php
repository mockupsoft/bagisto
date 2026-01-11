<?php

use Illuminate\Support\Facades\Route;
use MockupSoft\Companies\Http\Controllers\Admin\CompanyController;

/**
 * MockupSoft Companies admin routes.
 */
Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('mockupsoft/companies')->controller(CompanyController::class)->group(function () {
        Route::get('', 'index')->name('mockupsoft.companies.index');
        Route::get('{id}', 'show')->name('mockupsoft.companies.show');
        Route::post('', 'store')->name('mockupsoft.companies.store');
        Route::put('{id}', 'update')->name('mockupsoft.companies.update');
        Route::delete('{id}', 'destroy')->name('mockupsoft.companies.delete');
    });
});
