<h1>Tenant Dashboard</h1>

<div class="row">
    <div class="card">
        <h3>Tenant</h3>
        <div><strong>ID:</strong> {{ $tenant->id }}</div>
        <div><strong>Name:</strong> {{ $tenant->name }}</div>
        <div><strong>Status:</strong> <span class="badge">{{ $tenant->status }}</span></div>
        <div><strong>Created:</strong> {{ $tenant->created_at }}</div>
        <div><strong>Last error:</strong> <span class="muted">{{ $tenant->last_error ?? '-' }}</span></div>
    </div>

    <div class="card">
        <h3>Tenant DB</h3>
        <div><strong>Status:</strong> <span class="badge">{{ $tenant->database?->status ?? '-' }}</span></div>
        <div><strong>Last error:</strong> <span class="muted">{{ $tenant->database?->last_error ?? '-' }}</span></div>
    </div>
</div>

<h2>Domains</h2>

<div class="card" style="margin-bottom: 16px;">
    <h3>Add Custom Domain</h3>
    <form method="POST" action="{{ route('merchant.portal.domains.add') }}">
        @csrf
        <input type="text" name="domain" placeholder="example.com" required>
        <input type="text" name="note" placeholder="note (optional)">
        <button type="submit">Add</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Domain</th>
            <th>Type</th>
            <th>Verified</th>
            <th>Last Check</th>
            <th>Failure</th>
            <th>Instructions</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($domains as $domain)
            @php
                $dns = $verificationService->getDnsInstruction($domain);
                $http = $verificationService->getHttpInstruction($domain);
            @endphp
            <tr>
                <td>{{ $domain->domain }}</td>
                <td>{{ $domain->type }}</td>
                <td>
                    @if ($domain->verified_at)
                        <span class="badge badge-ok">Yes</span>
                    @else
                        <span class="badge badge-bad">No</span>
                    @endif
                </td>
                <td class="muted">{{ $domain->last_checked_at ?? '-' }}</td>
                <td class="muted">{{ $domain->last_failure_reason ?? '-' }}</td>
                <td style="max-width:420px;">
                    <div class="muted"><strong>DNS TXT</strong>: host <code>{{ $dns['host'] }}</code>, value <code>{{ $dns['value'] }}</code></div>
                    <div class="muted"><strong>HTTP</strong>: url <code>{{ $http['url'] }}</code>, content <code>{{ $http['value'] }}</code></div>
                </td>
                <td>
                    @if ($domain->type === 'custom')
                        <form method="POST" action="{{ route('merchant.portal.domains.verify', ['domain' => $domain->id]) }}" style="margin-bottom:6px;">
                            @csrf
                            <select name="method">
                                <option value="dns_txt">DNS TXT</option>
                                <option value="http_file">HTTP File</option>
                            </select>
                            <button type="submit">Verify now</button>
                        </form>

                        <form method="POST" action="{{ route('merchant.portal.domains.rotate', ['domain' => $domain->id]) }}">
                            @csrf
                            <select name="method">
                                <option value="dns_txt">DNS TXT</option>
                                <option value="http_file">HTTP File</option>
                            </select>
                            <button type="submit">Rotate token</button>
                        </form>
                    @else
                        <span class="muted">N/A</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
