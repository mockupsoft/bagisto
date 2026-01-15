<h1>Store Settings</h1>

<form method="POST" action="{{ route('merchant.settings.update') }}">
    @csrf

    <div style="margin-bottom: 10px;">
        <label>Store Name</label><br>
        <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? $tenant->store_name) }}" required>
    </div>

    <div style="margin-bottom: 10px;">
        <label>Support Email</label><br>
        <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? '') }}">
    </div>

    <div style="margin-bottom: 10px;">
        <label>Default Country</label><br>
        <select name="default_country">
            <option value="TR" @selected(old('default_country', $settings['default_country'] ?? 'TR') === 'TR')>TR</option>
        </select>
    </div>

    <div style="margin-bottom: 10px;">
        <label>Timezone</label><br>
        <input type="text" name="timezone" value="{{ old('timezone', $settings['timezone'] ?? 'Europe/Istanbul') }}">
    </div>

    <button type="submit">Save</button>
</form>
