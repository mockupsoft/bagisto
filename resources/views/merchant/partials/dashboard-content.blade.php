<h1>Merchant Dashboard</h1>

@if(isset($stats) && !empty($stats))
<div class="row mb-6">
    <div class="card">
        <h3>Orders</h3>
        <div><strong>Total:</strong> {{ $stats['total_orders'] ?? 0 }}</div>
        <div><strong>Today:</strong> {{ $stats['today_orders'] ?? 0 }}</div>
        <div><strong>This Month:</strong> {{ $stats['month_orders'] ?? 0 }}</div>
    </div>

    <div class="card">
        <h3>Revenue</h3>
        <div><strong>Total:</strong> {{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
        <div><strong>This Month:</strong> {{ number_format($stats['month_revenue'] ?? 0, 2) }}</div>
    </div>

    <div class="card">
        <h3>Customers</h3>
        <div><strong>Total:</strong> {{ $stats['total_customers'] ?? 0 }}</div>
    </div>

    <div class="card">
        <h3>Products</h3>
        <div><strong>Total:</strong> {{ $stats['total_products'] ?? 0 }}</div>
    </div>
</div>

@if(isset($stats['recent_orders']) && $stats['recent_orders']->count() > 0)
<div class="card mb-6">
    <h3>Recent Orders</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['recent_orders'] as $order)
            <tr>
                <td>{{ $order->increment_id }}</td>
                <td>{{ $order->customer_email }}</td>
                <td>{{ number_format($order->base_grand_total, 2) }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endif

<div class="row mb-6">
    <div class="card">
        <h3>Tenant</h3>
        <div><strong>ID:</strong> {{ $tenant->id }}</div>
        <div><strong>Name:</strong> {{ $tenant->name }}</div>
        <div><strong>Status:</strong> <span class="badge">{{ $tenant->status }}</span></div>
        <div><strong>Created:</strong> {{ $tenant->created_at }}</div>
    </div>

    <div class="card">
        <h3>Tenant DB</h3>
        <div><strong>Status:</strong> <span class="badge">{{ $tenant->database?->status ?? '-' }}</span></div>
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
