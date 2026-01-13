<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Merchant Registration - Step 2</title>
</head>
<body>
    <h1>Merchant Registration (Step 2/3)</h1>

    <p>
        <a href="{{ route('merchant.register.step1') }}">Back</a>
    </p>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('merchant.register.postStep2') }}">
        @csrf

        <div>
            <label>First Name</label>
            <input type="text" name="first_name" value="{{ old('first_name', data_get($data, 'step2.first_name')) }}" required />
        </div>

        <div>
            <label>Last Name</label>
            <input type="text" name="last_name" value="{{ old('last_name', data_get($data, 'step2.last_name')) }}" required />
        </div>

        <div>
            <label>Phone (optional)</label>
            <input type="text" name="phone" value="{{ old('phone', data_get($data, 'step2.phone')) }}" />
        </div>

        <button type="submit">Continue</button>
    </form>
</body>
</html>
