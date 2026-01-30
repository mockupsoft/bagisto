<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.components.layouts.sidebar.tenants')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.components.layouts.sidebar.tenants')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('tenants.create'))
                <a
                    href="{{ route('admin.tenants.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.tenants.create.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.tenants.list.before') !!}

    <!-- Datagrid -->
    <x-admin::datagrid :src="route('admin.tenants.index')" />

    {!! view_render_event('bagisto.admin.tenants.list.after') !!}
</x-admin::layouts>
