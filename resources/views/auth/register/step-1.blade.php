@php
    $step1Data = $data['step1'] ?? [];
@endphp

@component('auth.register.layout', ['currentStep' => 1, 'data' => $data])
    <h2 class="mb-6 text-2xl font-bold text-navyBlue">
        Authentication Credentials
    </h2>

    <x-shop::form :action="route('merchant.register.postStep1')">
        <!-- Email -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Email Address
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="email"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="email"
                rules="required|email"
                :value="old('email', $step1Data['email'] ?? '')"
                label="Email Address"
                placeholder="email@example.com"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="email" />
        </x-shop::form.control-group>

        <!-- Password -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Password
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="password"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="password"
                rules="required|min:8"
                value=""
                label="Password"
                placeholder="Password"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="password" />
        </x-shop::form.control-group>

        <!-- Confirm Password -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Confirm Password
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="password"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="password_confirmation"
                rules="required|confirmed:@password"
                value=""
                label="Confirm Password"
                placeholder="Confirm Password"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="password_confirmation" />
        </x-shop::form.control-group>

        <!-- Continue Button -->
        <div class="mt-8">
            <button
                class="primary-button m-0 mx-auto block w-full max-w-[374px] rounded-2xl px-11 py-4 text-center text-base max-md:max-w-full max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
                type="submit"
            >
                Continue
            </button>
        </div>
    </x-shop::form>
@endcomponent
