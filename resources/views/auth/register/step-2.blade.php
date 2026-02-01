@php
    $step1Data = $data['step1'] ?? [];
    $step2Data = $data['step2'] ?? [];
@endphp

@component('auth.register.layout', ['currentStep' => 2, 'data' => $data])
    <h2 class="mb-6 text-2xl font-bold text-navyBlue">
        Personal Details
    </h2>

    <x-shop::form :action="route('merchant.register.postStep2')">
        <!-- First Name -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                First Name
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="first_name"
                rules="required"
                :value="old('first_name', $step2Data['first_name'] ?? '')"
                label="First Name"
                placeholder="First Name"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="first_name" />
        </x-shop::form.control-group>

        <!-- Last Name -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="required">
                Last Name
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="last_name"
                rules="required"
                :value="old('last_name', $step2Data['last_name'] ?? '')"
                label="Last Name"
                placeholder="Last Name"
                aria-required="true"
            />

            <x-shop::form.control-group.error control-name="last_name" />
        </x-shop::form.control-group>

        <!-- Phone -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label>
                Phone
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                name="phone"
                :value="old('phone', $step2Data['phone'] ?? '')"
                label="Phone"
                placeholder="Phone (optional)"
            />

            <x-shop::form.control-group.error control-name="phone" />
        </x-shop::form.control-group>

        <!-- Continue Button -->
        <div class="mt-8 flex gap-4">
            <a
                href="{{ route('merchant.register.step1') }}"
                class="secondary-button flex-1 rounded-2xl px-11 py-4 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
            >
                Back
            </a>
            <button
                class="primary-button flex-1 rounded-2xl px-11 py-4 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
                type="submit"
            >
                Continue
            </button>
        </div>
    </x-shop::form>
@endcomponent
