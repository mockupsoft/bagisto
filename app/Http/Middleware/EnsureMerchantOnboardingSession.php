<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMerchantOnboardingSession
{
    /**
     * Ensure merchant onboarding session tenant id exists.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('onboarding.merchant_register.tenant_id')) {
            abort(403);
        }

        return $next($request);
    }
}
