@props(['currentStep' => 1, 'data' => []])

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <x-slot:title>
        Merchant Registration
    </x-slot>

    <div class="container mt-20 max-1180:px-5 max-md:mt-12">
        <!-- Logo -->
        <div class="flex items-center justify-center mb-8">
            <a
                href="{{ route('shop.home.index') }}"
                class="m-[0_auto_20px_auto]"
                aria-label="Bagisto"
            >
                <img
                    src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                    width="131"
                    height="29"
                >
            </a>
        </div>

        <div class="m-auto w-full max-w-[1200px]">
            <!-- Header Section -->
            <div class="mb-8 text-center">
                <h1 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl font-bold">
                    Merchant Registration
                </h1>
                <p class="mt-4 text-xl text-zinc-500 max-sm:mt-2 max-sm:text-sm">
                    Become a merchant and create your own store hassle free without worrying about installing and managing the server. You just need to signup, upload product data and get your e-commerce store.
                </p>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Progress Steps (Left Side) -->
                <div class="w-full lg:w-1/3">
                    <div class="rounded-xl border border-zinc-200 bg-white p-6 max-lg:mb-6">
                        <div class="space-y-6">
                            <!-- Step 1 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if ($currentStep > 1)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-navyBlue text-white">
                                            <span class="icon-check-box text-xl"></span>
                                        </div>
                                    @elseif ($currentStep == 1)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-navyBlue bg-navyBlue text-white">
                                            <span class="text-xl font-bold">1</span>
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-zinc-300 text-zinc-400">
                                            <span class="text-xl font-bold">1</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold {{ $currentStep >= 1 ? 'text-navyBlue' : 'text-zinc-400' }}">
                                        Authentication Credentials
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        Enter Authentication credentials like email, password and confirm password.
                                    </p>
                                </div>
                            </div>

                            <!-- Connector Line -->
                            <div class="ml-5 h-8 border-l-2 border-dashed {{ $currentStep > 1 ? 'border-navyBlue' : 'border-zinc-300' }}"></div>

                            <!-- Step 2 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if ($currentStep > 2)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-navyBlue text-white">
                                            <span class="icon-check-box text-xl"></span>
                                        </div>
                                    @elseif ($currentStep == 2)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-navyBlue bg-navyBlue text-white">
                                            <span class="text-xl font-bold">2</span>
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-zinc-300 text-zinc-400">
                                            <span class="text-xl font-bold">2</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold {{ $currentStep >= 2 ? 'text-navyBlue' : 'text-zinc-400' }}">
                                        Personal Details
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        Enter Personal Details like first name, last name and phone number.
                                    </p>
                                </div>
                            </div>

                            <!-- Connector Line -->
                            <div class="ml-5 h-8 border-l-2 border-dashed {{ $currentStep > 2 ? 'border-navyBlue' : 'border-zinc-300' }}"></div>

                            <!-- Step 3 -->
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    @if ($currentStep == 3)
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-navyBlue bg-navyBlue text-white">
                                            <span class="text-xl font-bold">3</span>
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-zinc-300 text-zinc-400">
                                            <span class="text-xl font-bold">3</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold {{ $currentStep >= 3 ? 'text-navyBlue' : 'text-zinc-400' }}">
                                        Organization Details
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        Enter Organisation Details like user name, organisation name.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Content (Right Side) -->
                <div class="w-full lg:w-2/3">
                    <div class="rounded-xl border border-zinc-200 bg-white p-8 max-md:p-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/merchant-registration.js') }}"></script>
    @endpush
</x-shop::layouts>
