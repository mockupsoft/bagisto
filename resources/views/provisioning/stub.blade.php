<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Provisioning</title>
</head>
<body>
    <h1>Provisioning</h1>

    <p>Provisioning will start in Patch-9B.</p>

    @php
        $data = session('onboarding.merchant_register', []);
        if (isset($data['step1']['password_encrypted'])) {
            $data['step1']['password_encrypted'] = '***';
        }
    @endphp

    <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

    <p>
        <a href="{{ route('merchant.register.step1') }}">Start over</a>
    </p>
</body>
</html>
