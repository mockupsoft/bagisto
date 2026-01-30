<x-admin::layouts>
    <x-slot:title>
        Tenant - {{ $tenant->name }}
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                {{ $tenant->name }}
            </h1>

            @if($tenant->status === 'active')
                <span class="label-active mx-1.5 text-sm">
                    Active
                </span>
            @elseif($tenant->status === 'inactive')
                <span class="label-canceled mx-1.5 text-sm">
                    Inactive
                </span>
            @elseif($tenant->status === 'provisioning')
                <span class="label-pending mx-1.5 text-sm">
                    Provisioning
                </span>
            @else
                <span class="label-canceled mx-1.5 text-sm">
                    {{ ucfirst($tenant->status) }}
                </span>
        @endif

            @if($tenant->database?->status === 'ready')
                <span class="label-active mx-1.5 text-sm">
                    DB Ready
                </span>
            @elseif($tenant->database?->status === 'failed')
                <span class="label-canceled mx-1.5 text-sm">
                    DB Failed
                </span>
        @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('admin.tenants.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.customers.customers.view.back-btn')
            </a>

            <form method="POST" action="{{ route('admin.tenants.retry', ['tenant' => $tenant->id]) }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="secondary-button"
                >
                    Retry Provisioning
                </button>
            </form>

            <form method="POST" action="{{ route('admin.tenants.toggle', ['tenant' => $tenant->id]) }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="{{ $tenant->status === 'active' ? 'danger-button' : 'primary-button' }}"
                >
                    {{ $tenant->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
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

    <!-- Tenant Store Navigation -->
    <div class="mt-6 flex justify-center gap-4 bg-white dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 pt-2 max-sm:hidden border-b border-gray-200">
        <a
            href="{{ route('admin.tenants.products.index', $tenant->id) }}"
            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium transition {{ request()->routeIs('admin.tenants.products.*') ? 'border-blue-600 border-b-2 text-blue-600' : 'text-gray-300' }}"
        >
            @lang('admin::app.catalog.products.index.title')
        </a>

        <a
            href="{{ route('admin.tenants.categories.index', $tenant->id) }}"
            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium transition {{ request()->routeIs('admin.tenants.categories.*') ? 'border-blue-600 border-b-2 text-blue-600' : 'text-gray-300' }}"
        >
            @lang('admin::app.catalog.categories.index.title')
        </a>

        <a
            href="{{ route('admin.tenants.attributes.index', $tenant->id) }}"
            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium transition {{ request()->routeIs('admin.tenants.attributes.*') ? 'border-blue-600 border-b-2 text-blue-600' : 'text-gray-300' }}"
        >
            @lang('admin::app.catalog.attributes.index.title')
        </a>

        <a
            href="{{ route('admin.tenants.customers.index', $tenant->id) }}"
            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium transition {{ request()->routeIs('admin.tenants.customers.*') ? 'border-blue-600 border-b-2 text-blue-600' : 'text-gray-300' }}"
        >
            @lang('admin::app.customers.customers.index.title')
        </a>

        <a
            href="{{ route('admin.tenants.orders.index', $tenant->id) }}"
            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium transition {{ request()->routeIs('admin.tenants.orders.*') ? 'border-blue-600 border-b-2 text-blue-600' : 'text-gray-300' }}"
        >
            @lang('admin::app.sales.orders.index.title')
        </a>
    </div>

    <!-- Main Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Section -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Tenant Information Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Tenant Information
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Name
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Slug
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->slug }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Store Name
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->store_name ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status
                        </p>
                        <p class="mt-1">
                            @if($tenant->status === 'active')
                                <span class="label-active text-sm">Active</span>
                            @elseif($tenant->status === 'inactive')
                                <span class="label-canceled text-sm">Inactive</span>
                            @else
                                <span class="label-pending text-sm">{{ ucfirst($tenant->status) }}</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Created At
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>

                    @if($tenant->last_error)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Last Error
                        </p>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ $tenant->last_error }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Database Information Card -->
            @if($tenant->database)
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Database Information
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status
                        </p>
                        <p class="mt-1">
                            @if($tenant->database->status === 'ready')
                                <span class="label-active text-sm">Ready</span>
                            @elseif($tenant->database->status === 'failed')
                                <span class="label-canceled text-sm">Failed</span>
                            @else
                                <span class="label-pending text-sm">{{ ucfirst($tenant->database->status) }}</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Host
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->database->database_host }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Database Name
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->database->database_name ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Database Username
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->database->database_username ?: '-' }}
                        </p>
                    </div>

                    @if($tenant->database->last_error)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Last Error
                        </p>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ $tenant->database->last_error }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Domains Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Domains
                </p>

                @if($tenant->domains->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        Domain
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        Type
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        Actions
                                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenant->domains as $domain)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                    {{ $domain->domain }}
                                                </p>
                                                @if($domain->is_primary)
                                                    <span class="label-active text-xs">Primary</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                            {{ ucfirst($domain->type) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($domain->verified_at)
                                                <span class="label-active text-sm">Verified</span>
                                            @else
                                                <span class="label-pending text-sm">Not Verified</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                @if($domain->type === 'custom')
                                                    <form method="POST" action="{{ route('admin.domains.verify', ['domain' => $domain->id]) }}" class="inline">
                                @csrf
                                                        <select
                                                            name="method"
                                                            class="rounded-md border border-gray-300 bg-white px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                        >
                                                            <option value="dns_txt">DNS TXT</option>
                                                            <option value="http_file">HTTP File</option>
                                                        </select>
                                                        <button
                                                            type="submit"
                                                            class="secondary-button ml-2"
                                                        >
                                                            Verify
                                                        </button>
                            </form>

                                                    <form method="POST" action="{{ route('admin.domains.rotate', ['domain' => $domain->id]) }}" class="inline">
                                @csrf
                                                        <button
                                                            type="submit"
                                                            class="secondary-button"
                                                        >
                                                            Rotate Token
                                                        </button>
                            </form>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        No domains configured.
                    </p>
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
                            Domains
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->domains->count() }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Primary Domain
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $tenant->primaryDomain?->domain ?: '-' }}
                        </p>
                    </div>

                    @if($tenant->database)
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Database Status
                        </p>
                        <p class="text-base font-semibold">
                            @if($tenant->database->status === 'ready')
                                <span class="label-active text-sm">Ready</span>
                            @else
                                <span class="label-pending text-sm">{{ ucfirst($tenant->database->status) }}</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links Card -->
            @php
                $urlService = app(\App\Services\Tenant\TenantUrlService::class);
                $adminUrl = $urlService->getAdminUrl($tenant);
                $storefrontUrl = $urlService->getStorefrontUrl($tenant);
                $hasAccessibleDomain = $urlService->hasAccessibleDomain($tenant);
            @endphp
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.tenants.show.quick-links')
                </p>

                <div class="flex flex-col gap-3">
                    <!-- Tenant Admin Panel Link -->
                    <div>
                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.show.admin-panel-link')
                        </p>
                        @if($hasAccessibleDomain && $adminUrl)
                            <a
                                href="{{ $adminUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="secondary-button inline-block w-full text-center"
                            >
                                @lang('admin::app.tenants.show.open-admin-panel')
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="transparent-button inline-block w-full cursor-not-allowed opacity-50"
                            >
                                @lang('admin::app.tenants.show.no-domain-warning')
                            </button>
                        @endif
                    </div>

                    <!-- Tenant Storefront Link -->
                    <div>
                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.show.storefront-link')
                        </p>
                        @if($hasAccessibleDomain && $storefrontUrl)
                            <a
                                href="{{ $storefrontUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="secondary-button inline-block w-full text-center"
                            >
                                @lang('admin::app.tenants.show.open-storefront')
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="transparent-button inline-block w-full cursor-not-allowed opacity-50"
                            >
                                @lang('admin::app.tenants.show.no-domain-warning')
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin User Information Card -->
            @if(isset($merchantUser) && $merchantUser)
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.tenants.show.admin-user-info')
                </p>

                <div class="flex flex-col gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.show.admin-name')
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                            {{ $merchantUser->name ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.show.admin-email')
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                            {{ $merchantUser->email ?: '-' }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Provisioning Info Card -->
            @if($tenant->provisioning_started_at || $tenant->provisioning_finished_at)
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Provisioning
                </p>

                <div class="flex flex-col gap-3">
                    @if($tenant->provisioning_started_at)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Started At
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                            {{ $tenant->provisioning_started_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    @endif

                    @if($tenant->provisioning_finished_at)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Finished At
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                            {{ $tenant->provisioning_finished_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
