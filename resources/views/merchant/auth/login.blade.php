<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Merchant Login</title>
</head>
<body style="font-family:Arial, sans-serif; padding: 20px;">
    <h1>Merchant Login</h1>

    @if ($errors->any())
        <div style="background:#fee2e2; padding: 10px; margin-bottom: 10px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('merchant.login.post') }}">
        @csrf
        <div style="margin-bottom: 10px;">
            <label>Email</label><br>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>
                <input type="checkbox" name="remember" value="1"> Remember
            </label>
        </div>

        <button type="submit">Login</button>
    </form>
</body>
</html>
