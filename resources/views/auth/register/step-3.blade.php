@php
    $step1Data = $data['step1'] ?? [];
    $step2Data = $data['step2'] ?? [];
    $step3Data = $data['step3'] ?? [];
@endphp

@component('auth.register.layout', ['currentStep' => 3, 'data' => $data])
    <h2 class="mb-6 text-2xl font-bold text-navyBlue">
        Organization Details
    </h2>

    <x-shop::form :action="route('merchant.register.complete')">
        <!-- Username / Subdomain -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Subdomain
            </x-shop::form.control-group.label>

            <div class="flex items-center gap-2">
                <x-shop::form.control-group.control
                    type="text"
                    class="px-6 py-4 max-md:py-3 max-sm:py-2"
                    name="subdomain"
                    rules="required|min:3|max:30|regex:/^(?!-)[a-z0-9-]+(?<!-)$/|unique:tenants,subdomain"
                    :value="old('subdomain', $step3Data['subdomain'] ?? '')"
                    label="Subdomain"
                    placeholder="yourstore"
                    aria-required="true"
                />
                <span class="text-lg text-zinc-500">.{{ config('saas.base_domain', 'example.test') }}</span>
            </div>

            <p class="mt-1 text-sm text-zinc-500">
                This will be your store URL: yourstore.{{ config('saas.base_domain', 'example.test') }}
            </p>

            <x-shop::form.control-group.error control-name="subdomain" />
        </x-shop::form.control-group>

        <!-- Organization Name / Store Name -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Organization Name
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="store_name"
                rules="required|max:120"
                :value="old('store_name', $step3Data['store_name'] ?? '')"
                label="Organization Name"
                placeholder="Your Store Name"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="store_name" />
        </x-shop::form.control-group>

        <!-- Terms & Conditions -->
        <x-shop::form.control-group>
            <div class="flex select-none items-center gap-1.5">
                <x-shop::form.control-group.control
                    type="checkbox"
                    name="terms_accepted"
                    id="terms_accepted"
                    value="1"
                    rules="required|accepted"
                    :checked="old('terms_accepted', $step3Data['terms_accepted'] ?? false)"
                />

                <label
                    class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm"
                    for="terms_accepted"
                >
                    I agree to the Terms & Conditions
                </label>
            </div>

            <x-shop::form.control-group.error control-name="terms_accepted" />
        </x-shop::form.control-group>

        <!-- Submit Button -->
        <div class="mt-8 flex gap-4">
            <a
                href="{{ route('merchant.register.step2') }}"
                class="secondary-button flex-1 rounded-2xl px-11 py-4 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
            >
                Back
            </a>
            <button
                class="primary-button flex-1 rounded-2xl px-11 py-4 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
                type="submit"
            >
                Complete Registration
            </button>
        </div>
    </x-shop::form>
@endcomponent
