<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Merchant Registration - Step 3</title>
</head>
<body>
    <h1>Merchant Registration (Step 3/3)</h1>

    <p>
        <a href="{{ route('merchant.register.step2') }}">Back</a>
    </p>

    <h3>Summary</h3>
    <ul>
        <li>Email: {{ data_get($data, 'step1.email') }}</li>
        <li>Name: {{ data_get($data, 'step2.first_name') }} {{ data_get($data, 'step2.last_name') }}</li>
    </ul>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('merchant.register.complete') }}">
        @csrf

        <div>
            <label>Store Name</label>
            <input type="text" name="store_name" value="{{ old('store_name', data_get($data, 'step3.store_name')) }}" required />
        </div>

        <div>
            <label>Subdomain</label>
            <input type="text" name="subdomain" value="{{ old('subdomain', data_get($data, 'step3.subdomain')) }}" required />
            <small>Example: my-store</small>
        </div>

        <div>
            <label>
                <input type="checkbox" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required />
                I accept the terms.
            </label>
        </div>

        <button type="submit">Complete Registration</button>
    </form>
</body>
</html>
