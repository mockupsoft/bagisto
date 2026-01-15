<x-admin::layouts>
    <x-slot:title>
        Tenant #{{ $tenant->id }}
    </x-slot>

    <div class="content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="text-xl font-bold mb-4">Tenant #{{ $tenant->id }}</h1>

        <div class="mb-4">
            <div><strong>Name:</strong> {{ $tenant->name }}</div>
            <div><strong>Slug:</strong> {{ $tenant->slug }}</div>
            <div><strong>Status:</strong> {{ $tenant->status }}</div>
            <div><strong>Store Name:</strong> {{ $tenant->store_name }}</div>
            <div><strong>Last error:</strong> {{ $tenant->last_error ?? '-' }}</div>
            <div><strong>Created:</strong> {{ $tenant->created_at }}</div>
        </div>

        <div class="mb-4">
            <h2 class="font-bold">Tenant Database</h2>
            <div><strong>Status:</strong> {{ $tenant->database?->status ?? '-' }}</div>
            <div><strong>Host:</strong> {{ $tenant->database?->database_host ?? '-' }}</div>
            <div><strong>DB Name:</strong> {{ $tenant->database?->database_name ? str_repeat('*', max(0, strlen($tenant->database->database_name) - 4)) . substr($tenant->database->database_name, -4) : '-' }}</div>
            <div><strong>Last error:</strong> {{ $tenant->database?->last_error ?? '-' }}</div>
        </div>

        <div class="mb-4">
            <form method="POST" action="{{ route('admin.tenants.retry', ['tenant' => $tenant->id]) }}" style="display:inline;">
                @csrf
                <button type="submit">Retry Provisioning</button>
            </form>

            <form method="POST" action="{{ route('admin.tenants.toggle', ['tenant' => $tenant->id]) }}" style="display:inline; margin-left: 8px;">
                @csrf
                <button type="submit">{{ $tenant->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
            </form>
        </div>

        <h2 class="font-bold mt-6">Domains</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Domain</th>
                    <th>Type</th>
                    <th>Primary</th>
                    <th>Verified</th>
                    <th>Last Check</th>
                    <th>Failure</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenant->domains as $domain)
                    <tr>
                        <td>{{ $domain->id }}</td>
                        <td>{{ $domain->domain }}</td>
                        <td>{{ $domain->type }}</td>
                        <td>{{ $domain->is_primary ? 'yes' : 'no' }}</td>
                        <td>{{ $domain->verified_at ? 'yes' : 'no' }}</td>
                        <td>{{ $domain->last_checked_at ?? '-' }}</td>
                        <td>{{ $domain->last_failure_reason ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.domains.rotate', ['domain' => $domain->id]) }}" style="display:inline;">
                                @csrf
                                <button type="submit">Rotate Token</button>
                            </form>

                            <form method="POST" action="{{ route('admin.domains.verify', ['domain' => $domain->id]) }}" style="display:inline; margin-left: 8px;">
                                @csrf
                                <select name="method">
                                    <option value="dns_txt">DNS</option>
                                    <option value="http_file">HTTP</option>
                                </select>
                                <button type="submit">Verify</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            <a href="{{ route('admin.tenants.index') }}">Back</a>
        </div>
    </div>
</x-admin::layouts>
