<x-admin::layouts>
    <x-slot:title>
        Product - {{ $product->name ?? $product->sku }}
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                {{ $productFlat->name ?? $product->sku ?? 'Product #' . $product->id }}
            </h1>

            @if(isset($productFlat->status))
                @if($productFlat->status)
                    <span class="label-active mx-1.5 text-sm">
                        @lang('admin::app.catalog.products.index.datagrid.enabled')
                    </span>
                @else
                    <span class="label-canceled mx-1.5 text-sm">
                        @lang('admin::app.catalog.products.index.datagrid.disabled')
                    </span>
                @endif
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('admin.tenants.products.index', $tenant->id) }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.customers.customers.view.back-btn')
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Section -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Product Information Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Product Information
                </p>

                <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            SKU
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $product->sku ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Name
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $productFlat->name ?? '-' }}
                        </p>
                    </div>

                    @if(isset($product->type))
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Type
                            </p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                                {{ ucfirst($product->type) }}
                            </p>
                        </div>
                    @endif

                    @if(isset($productFlat->price))
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Price
                            </p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                                {{ core()->formatPrice($productFlat->price) }}
                            </p>
                        </div>
                    @endif

                    @if(isset($product->attribute_family))
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Attribute Family
                            </p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $product->attribute_family->name ?? '-' }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Status
                        </p>
                        <p class="mt-1">
                            @if(isset($productFlat->status) && $productFlat->status)
                                <span class="label-active text-sm">Active</span>
                            @else
                                <span class="label-canceled text-sm">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Categories Card -->
            @if($product->categories && $product->categories->count() > 0)
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Categories ({{ $product->categories->count() }})
                    </p>

                    <div class="grid gap-2">
                        @foreach ($product->categories as $category)
                            <div class="border-b border-gray-200 pb-2 last:border-none last:pb-0 dark:border-gray-800">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    {{ $category->name ?? 'Category #' . $category->id }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                            Product ID
                        </p>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ $product->id }}
                        </p>
                    </div>

                    @if(isset($product->created_at))
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Created At
                            </p>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $product->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    @endif

                    @if(isset($product->updated_at))
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Updated At
                            </p>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                {{ $product->updated_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
