<x-admin::layouts>
    <x-slot:title>
        Tenants
    </x-slot>

    <div class="content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="text-xl font-bold mb-4">Tenants</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>DB Status</th>
                    <th>Primary Domain</th>
                    <th>Verified</th>
                    <th>Last Error</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->id }}</td>
                        <td>{{ $tenant->name }}</td>
                        <td>{{ $tenant->slug }}</td>
                        <td>{{ $tenant->status }}</td>
                        <td>{{ $tenant->database?->status ?? '-' }}</td>
                        <td>{{ $tenant->primaryDomain?->domain ?? '-' }}</td>
                        <td>{{ $tenant->primaryDomain?->verified_at ? 'yes' : 'no' }}</td>
                        <td>{{ $tenant->last_error ?? '-' }}</td>
                        <td>{{ $tenant->created_at }}</td>
                        <td>
                            <a href="{{ route('admin.tenants.show', ['tenant' => $tenant->id]) }}">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $tenants->links() }}
        </div>
    </div>
</x-admin::layouts>
