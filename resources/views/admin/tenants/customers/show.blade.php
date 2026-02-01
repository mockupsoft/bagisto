<x-admin::layouts>
    <x-slot:title>
        Customer - {{ $customer->first_name }} {{ $customer->last_name }}
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                {{ $customer->first_name }} {{ $customer->last_name }}
            </h1>

            @if($customer->status)
                <span class="label-active mx-1.5 text-sm">
                    @lang('admin::app.customers.customers.view.active')
                </span>
            @else
                <span class="label-canceled mx-1.5 text-sm">
                    @lang('admin::app.customers.customers.view.inactive')
                </span>
            @endif

            @if($customer->is_suspended)
                <span class="label-canceled text-sm">
                    @lang('admin::app.customers.customers.view.suspended')
                </span>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('admin.tenants.customers.index', $tenant->id) }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.customers.customers.view.back-btn')
            </a>

            @if($customer->is_suspended)
                <form method="POST" action="{{ route('admin.tenants.customers.activate', [$tenant->id, $customer->id]) }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Activate
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.tenants.customers.suspend', [$tenant->id, $customer->id]) }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="secondary-button"
                    >
                        Suspend
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mt-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                {{ session('success') }}
            </p>
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                {{ session('error') }}
            </p>
        </div>
    @endif

    <!-- Main Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Section -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Customer Information Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.customers.customers.view.customer')
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.customers.customers.view.email')
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.customers.customers.view.phone')
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->phone ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.customers.customers.view.gender')
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->gender ? ucfirst($customer->gender) : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.customers.customers.view.date-of-birth')
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.customers.customers.view.group')
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->group?->name ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status
                        </p>
                        <p class="mt-1">
                            @if($customer->status)
                                <span class="label-active text-sm">Active</span>
                            @else
                                <span class="label-canceled text-sm">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Addresses Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.customers.customers.view.address.count', ['count' => $customer->addresses->count()])
                </p>

                @if($customer->addresses->count() > 0)
                    <div class="grid gap-4">
                        @foreach ($customer->addresses as $address)
                            <div class="border-b border-gray-200 pb-4 last:border-none last:pb-0 dark:border-gray-800">
                                @if($address->default_address)
                                    <span class="label-pending mb-2 inline-block text-sm">
                                        @lang('admin::app.customers.customers.view.default-address')
                                    </span>
                                @endif

                                <p class="font-semibold text-gray-800 dark:text-white">
                                    {{ $address->first_name }} {{ $address->last_name }}
                                    @if($address->company_name)
                                        ({{ $address->company_name }})
                                    @endif
                                </p>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    @if($address->address)
                                        {{ str_replace("\n", ', ', $address->address) }},
                                    @endif
                                    {{ $address->city }},
                                    {{ $address->state }},
                                    {{ $address->country }},
                                    {{ $address->postcode }}
                                </p>

                                @if($address->phone)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.customers.customers.view.phone'): {{ $address->phone }}
                                    </p>
                                @endif

                                @if($address->email)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.customers.customers.view.email'): {{ $address->email }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-5 py-2.5">
                        <img
                            src="{{ bagisto_asset('images/settings/address.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('admin::app.customers.customers.view.empty-title')
                            </p>
                            <p class="text-gray-400">
                                @lang('admin::app.customers.customers.view.empty-description')
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Orders Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex justify-between">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.customers.customers.view.orders.count', ['count' => $customer->orders->count()])
                    </p>
                    @if($customer->orders->count() > 0)
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.customers.customers.view.orders.total-revenue', [
                                'revenue' => core()->formatPrice($customer->orders->whereNotIn('status', ['canceled', 'closed'])->sum('base_grand_total'))
                            ])
                        </p>
                    @endif
                </div>

                @if($customer->orders->count() > 0)
                    <div class="grid gap-2">
                        @foreach ($customer->orders->take(10) as $order)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 last:border-none last:pb-0 dark:border-gray-800">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        #{{ $order->increment_id }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ $order->created_at->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        {{ core()->formatPrice($order->base_grand_total) }}
                                    </p>
                                    <span class="label-{{ $order->status }} text-sm">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center py-10">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.customers.customers.view.datagrid.orders.empty-order')
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
            <!-- Quick Stats Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Quick Stats
                </p>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Orders
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->orders->count() }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Addresses
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->addresses->count() }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Member Since
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $customer->created_at->format('M Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
