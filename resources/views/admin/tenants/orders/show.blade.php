<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sales.orders.view.title', ['order_id' => $order->increment_id])
    </x-slot>

    <!-- Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                @lang('admin::app.sales.orders.view.title', ['order_id' => $order->increment_id])
            </p>

            <!-- Order Status -->
            <span class="label-{{ $order->status }} text-sm mx-1.5">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('admin.tenants.orders.index', $tenant->id) }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.account.edit.back-btn')
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Section -->
        <div class="flex flex-col flex-1 gap-2 max-xl:flex-auto">
            <!-- Order Items Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex justify-between mb-4">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        Order Items ({{ $order->items->count() }})
                    </p>

                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.sales.orders.view.grand-total', ['grand_total' => core()->formatBasePrice($order->base_grand_total)])
                    </p>
                </div>

                @if($order->items->count() > 0)
                    <div class="grid gap-4">
                        @foreach ($order->items as $item)
                            <div class="flex justify-between gap-2.5 border-b border-gray-200 pb-4 last:border-none last:pb-0 dark:border-gray-800">
                                <div class="flex gap-2.5">
                                    <div class="grid place-content-start gap-1.5">
                                        <p class="text-base font-semibold text-gray-800 break-all dark:text-white">
                                            {{ $item->name }}
                                        </p>

                                        <div class="flex flex-col place-items-start gap-1.5">
                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.amount-per-unit', [
                                                    'amount' => core()->formatBasePrice($item->base_price),
                                                    'qty'    => $item->qty_ordered,
                                                ])
                                            </p>

                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sku', ['sku' => $item->sku])
                                            </p>

                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                {{ $item->qty_ordered ? trans('admin::app.sales.orders.view.item-ordered', ['qty_ordered' => $item->qty_ordered]) : '' }}
                                                {{ $item->qty_invoiced ? trans('admin::app.sales.orders.view.item-invoice', ['qty_invoiced' => $item->qty_invoiced]) : '' }}
                                                {{ $item->qty_shipped ? trans('admin::app.sales.orders.view.item-shipped', ['qty_shipped' => $item->qty_shipped]) : '' }}
                                                {{ $item->qty_refunded ? trans('admin::app.sales.orders.view.item-refunded', ['qty_refunded' => $item->qty_refunded]) : '' }}
                                                {{ $item->qty_canceled ? trans('admin::app.sales.orders.view.item-canceled', ['qty_canceled' => $item->qty_canceled]) : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-1 place-content-start">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        {{ core()->formatBasePrice($item->base_total + $item->base_tax_amount - $item->base_discount_amount) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center py-10">
                        <p class="text-base font-semibold text-gray-400">
                            No items found
                        </p>
                    </div>
                @endif
            </div>

            <!-- Order Summary Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Order Summary
                </p>

                <div class="grid gap-2 text-sm">
                    <div class="flex justify-between">
                        <p class="font-semibold text-gray-600 dark:text-gray-300">
                            @lang('admin::app.sales.orders.view.summary-sub-total')
                        </p>
                        <p class="font-semibold text-gray-600 dark:text-gray-300">
                            {{ core()->formatBasePrice($order->base_sub_total) }}
                        </p>
                    </div>

                    @if($order->base_tax_amount > 0)
                        <div class="flex justify-between">
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.summary-tax')
                            </p>
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                {{ core()->formatBasePrice($order->base_tax_amount) }}
                            </p>
                        </div>
                    @endif

                    @if($order->base_discount_amount > 0)
                        <div class="flex justify-between">
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.summary-discount')
                            </p>
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                {{ core()->formatBasePrice($order->base_discount_amount) }}
                            </p>
                        </div>
                    @endif

                    @if($order->base_shipping_amount > 0)
                        <div class="flex justify-between">
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.summary-shipping')
                            </p>
                            <p class="font-semibold text-gray-600 dark:text-gray-300">
                                {{ core()->formatBasePrice($order->base_shipping_amount) }}
                            </p>
                        </div>
                    @endif

                    <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-800">
                        <p class="font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.sales.orders.view.summary-grand-total')
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ core()->formatBasePrice($order->base_grand_total) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
            <!-- Customer Information Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Customer Information
                </p>

                <div class="grid gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Name
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $order->customer_first_name }} {{ $order->customer_last_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Email
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $order->customer_email }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Addresses Card -->
            @if($order->addresses && $order->addresses->count() > 0)
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Addresses
                    </p>

                    <div class="grid gap-4">
                        @foreach ($order->addresses as $address)
                            <div class="border-b border-gray-200 pb-4 last:border-none last:pb-0 dark:border-gray-800">
                                <p class="mb-2 font-semibold text-gray-800 dark:text-white">
                                    {{ ucfirst($address->address_type) }} Address
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $address->first_name }} {{ $address->last_name }}<br>
                                    @if($address->address)
                                        {{ str_replace("\n", ', ', $address->address) }},<br>
                                    @endif
                                    {{ $address->city }}, {{ $address->state }}, {{ $address->country }}<br>
                                    {{ $address->postcode }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Payment Information Card -->
            @if($order->payment)
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        Payment Information
                    </p>

                    <div class="grid gap-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Method
                            </p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $order->payment->method)) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Status
                            </p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                                {{ ucfirst($order->payment->status) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Information Card -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    Order Information
                </p>

                <div class="grid gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Order ID
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $order->increment_id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Created At
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $order->created_at->format('Y-m-d H:i:s') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Channel
                        </p>
                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white">
                            {{ $order->channel_name ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
