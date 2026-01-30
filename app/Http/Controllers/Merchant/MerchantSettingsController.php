<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantUser;
use App\Models\Tenant\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchantSettingsController extends Controller
{
    public function edit(): View
    {
        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        $tenant = Tenant::findOrFail($merchant->tenant_id);

        return view('merchant.settings', [
            'tenant' => $tenant,
            'settings' => $tenant->settings ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'support_email' => ['nullable', 'email'],
            'default_country' => ['nullable', 'in:TR'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $tenant = Tenant::findOrFail($merchant->tenant_id);

        $tenant->store_name = $validated['store_name'];

        $tenant->settings = [
            'store_name' => $validated['store_name'],
            'support_email' => $validated['support_email'] ?? null,
            'default_country' => $validated['default_country'] ?? 'TR',
            'timezone' => $validated['timezone'] ?? 'Europe/Istanbul',
        ];

        $tenant->save();

        return redirect()->route('merchant.settings.edit')->with('success', 'Settings saved.');
    }
}
