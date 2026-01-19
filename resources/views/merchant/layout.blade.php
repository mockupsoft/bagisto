<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Merchant Portal' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        header { padding: 12px 16px; background: #111827; color: #fff; display:flex; justify-content:space-between; }
        a { color: #2563eb; }
        main { padding: 16px; }
        .flash { padding: 10px; margin: 10px 0; border-radius: 6px; }
        .flash-success { background: #dcfce7; }
        .flash-error { background: #fee2e2; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f4f6; text-align:left; }
        .muted { color: #6b7280; }
        .badge { padding: 2px 6px; border-radius: 4px; background: #e5e7eb; }
        .badge-ok { background: #dcfce7; }
        .badge-bad { background: #fee2e2; }
        form { margin: 0; }
        input, select { padding: 6px; }
        .row { display:flex; gap: 16px; flex-wrap: wrap; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; flex: 1; min-width: 280px; }
    </style>
</head>
<body>
<header>
    <div>
        <strong>Merchant Portal</strong>
        <span class="muted">{{ auth()->guard('merchant')->user()?->email }}</span>
    </div>

    <div>
        <a href="{{ route('merchant.dashboard') }}" style="color:#fff; margin-right:12px;">Dashboard</a>
        <a href="{{ route('merchant.settings.edit') }}" style="color:#fff; margin-right:12px;">Settings</a>
        <form method="POST" action="{{ route('merchant.logout') }}" style="display:inline;">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</header>

<main>
    @if (session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
    @endif

    {{ $slot ?? '' }}
</main>
</body>
</html>
