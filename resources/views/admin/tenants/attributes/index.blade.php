<x-admin::layouts>
    <x-slot:title>
        Tenant Attributes - {{ $tenant->name }}
    </x-slot>

    <div class="flex gap-4 items-center justify-between">
        <div class="flex gap-4 items-center">
            <a
                href="{{ route('admin.tenants.show', $tenant->id) }}"
                class="icon-arrow-left text-2xl cursor-pointer"
            ></a>

            <h1 class="text-xl font-bold">
                Attributes - {{ $tenant->name }}
            </h1>
        </div>

        <div class="flex gap-2">
            <a
                href="{{ route('admin.tenants.show', $tenant->id) }}"
                class="secondary-button"
            >
                Back to Tenant
            </a>
        </div>
    </div>

    <div class="mt-4">
        <x-admin::datagrid :src="route('admin.tenants.attributes.index', $tenant->id)" />
    </div>
</x-admin::layouts>
