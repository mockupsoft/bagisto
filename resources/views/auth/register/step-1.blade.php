<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Merchant Registration - Step 1</title>
</head>
<body>
    <h1>Merchant Registration (Step 1/3)</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('merchant.register.postStep1') }}">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', data_get($data, 'step1.email')) }}" required />
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required />
        </div>

        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required />
        </div>

        <button type="submit">Continue</button>
    </form>
</body>
</html>
