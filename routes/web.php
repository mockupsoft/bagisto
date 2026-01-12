<?php

use Illuminate\Support\Facades\Route;

if (app()->environment(['local', 'testing'])) {
    Route::middleware(['tenant.resolve'])->get('/__tenant_ping', function () {
        return response('ok');
    });
}
