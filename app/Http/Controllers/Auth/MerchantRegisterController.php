<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantRegisterStep1Request;
use App\Http\Requests\MerchantRegisterStep2Request;
use App\Http\Requests\MerchantRegisterStep3Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MerchantRegisterController extends Controller
{
    protected const SESSION_KEY = 'onboarding.merchant_register';

    public function showStep1(): View
    {
        return view('auth.register.step-1', [
            'data' => session(self::SESSION_KEY, []),
        ]);
    }

    public function postStep1(MerchantRegisterStep1Request $request): RedirectResponse
    {
        $validated = $request->validated();

        session()->put(self::SESSION_KEY . '.step1', [
            'email' => $validated['email'],
            // Patch-9B will persist the user. For Patch-9A we temporarily store password encrypted.
            'password_encrypted' => encrypt($validated['password']),
        ]);

        return redirect()->route('merchant.register.step2');
    }

    public function showStep2(): RedirectResponse|View
    {
        if (! session()->has(self::SESSION_KEY . '.step1.email')) {
            return redirect()->route('merchant.register.step1');
        }

        return view('auth.register.step-2', [
            'data' => session(self::SESSION_KEY, []),
        ]);
    }

    public function postStep2(MerchantRegisterStep2Request $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY . '.step1.email')) {
            return redirect()->route('merchant.register.step1');
        }

        $validated = $request->validated();

        session()->put(self::SESSION_KEY . '.step2', [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('merchant.register.step3');
    }

    public function showStep3(): RedirectResponse|View
    {
        if (! session()->has(self::SESSION_KEY . '.step1.email')) {
            return redirect()->route('merchant.register.step1');
        }

        if (! session()->has(self::SESSION_KEY . '.step2.first_name')) {
            return redirect()->route('merchant.register.step2');
        }

        return view('auth.register.step-3', [
            'data' => session(self::SESSION_KEY, []),
        ]);
    }

    public function complete(MerchantRegisterStep3Request $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY . '.step1.email')) {
            return redirect()->route('merchant.register.step1');
        }

        if (! session()->has(self::SESSION_KEY . '.step2.first_name')) {
            return redirect()->route('merchant.register.step2');
        }

        $validated = $request->validated();

        session()->put(self::SESSION_KEY . '.step3', [
            'store_name' => $validated['store_name'],
            'subdomain' => $validated['subdomain'],
            'terms_accepted' => (bool) $validated['terms_accepted'],
        ]);

        session()->put(self::SESSION_KEY . '.completed_at', now()->toIso8601String());

        return redirect()->route('merchant.provisioning.stub');
    }
}
