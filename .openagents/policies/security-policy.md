# Güvenlik Politikası

Bu politika, tüm kod değişikliklerinde uyulması gereken güvenlik kurallarını tanımlar.

---

## Temel Kurallar

### 1. Hardcoded Secret Yasağı

- **Kural:** Kodda hiçbir secret, API key, password veya token hardcode edilmez
- **Çözüm:** Environment variable veya config dosyaları kullanılır
- **İhlal durumunda:** Patch reddedilir

```php
// YANLIŞ ❌
$apiKey = 'sk-1234567890abcdef';
$dbPassword = 'mypassword123';

// DOĞRU ✅
$apiKey = config('services.api.key');
$apiKey = env('API_KEY');
```

### 2. Sensitive Data Logging Yasağı

- **Kural:** Sensitive data log'lanmaz
- **Örnekler:** Password, credit card, API key, token

```php
// YANLIŞ ❌
Log::info('User login', ['password' => $password]);

// DOĞRU ✅
Log::info('User login', ['user_id' => $user->id]);
```

---

## Input Validation

### Zorunlu Validation

Tüm kullanıcı girdileri validate edilmelidir:

```php
// Controller'da
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'price' => 'required|numeric|min:0',
    ]);
}

// Form Request kullanımı (tercih edilen)
public function store(StoreProductRequest $request)
{
    // Validation otomatik yapılır
}
```

### XSS Koruması

- Blade template'lerde `{{ }}` kullanılır (auto-escape)
- Raw output için `{!! !!}` kullanılmaz (zorunlu değilse)

```php
// DOĞRU ✅
{{ $userInput }}

// DİKKATLİ ⚠️ (sadece güvenli HTML için)
{!! $trustedHtml !!}
```

### SQL Injection Koruması

- Eloquent veya Query Builder kullanılır
- Raw query'lerde binding kullanılır

```php
// DOĞRU ✅
User::where('email', $email)->first();
DB::table('users')->where('email', $email)->first();

// RAW QUERY (binding ile) ✅
DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// YANLIŞ ❌
DB::select("SELECT * FROM users WHERE email = '$email'");
```

---

## Auth ve Permission Kontrolü

### Route Koruması

```php
// Middleware kullanımı
Route::middleware(['auth:admin'])->group(function () {
    // Protected routes
});

// Permission middleware
Route::middleware(['admin', 'permission:catalog.products.edit'])->group(function () {
    // Permission-protected routes
});
```

### Controller Kontrolü

```php
public function edit($id)
{
    // Auth check
    if (! auth()->guard('admin')->check()) {
        abort(403);
    }
    
    // Permission check
    if (! bouncer()->hasPermission('catalog.products.edit')) {
        abort(403);
    }
}
```

### Bagisto ACL Kullanımı

```php
// acl.php config
return [
    [
        'key' => 'catalog',
        'name' => 'admin::app.acl.catalog',
        'route' => 'admin.catalog.index',
        'sort' => 1,
    ],
    [
        'key' => 'catalog.products',
        'name' => 'admin::app.acl.products',
        'route' => 'admin.catalog.products.index',
        'sort' => 1,
    ],
];
```

---

## CSRF Koruması

### Form'larda CSRF Token

```blade
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

### AJAX Request'lerde

```javascript
// Meta tag'den al
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

---

## Dependency Değişiklikleri

### Onay Gerekliliği

Aşağıdaki değişiklikler için **açık onay** gereklidir:

- Yeni composer package ekleme
- Yeni npm package ekleme
- Mevcut package versiyonu değiştirme
- Package kaldırma

### Güvenlik Kontrolü

Yeni dependency eklemeden önce:

1. Package'ın güvenilirliğini kontrol et
2. Bilinen vulnerability'leri kontrol et
3. Maintenance durumunu kontrol et
4. License uyumluluğunu kontrol et

```bash
# Composer security check
composer audit

# NPM security check
npm audit
```

---

## File Upload Güvenliği

### Validation

```php
$request->validate([
    'file' => 'required|file|mimes:jpg,png,pdf|max:10240',
]);
```

### Storage

- Public klasöre direkt upload yapılmaz
- Storage disk kullanılır
- Dosya adı sanitize edilir

```php
// DOĞRU ✅
$path = $request->file('document')->store('documents', 'private');

// YANLIŞ ❌
$request->file('document')->move(public_path('uploads'), $originalName);
```

---

## Mass Assignment Koruması

### Model'de Tanımlama

```php
// Fillable (tercih edilen)
protected $fillable = ['name', 'email', 'price'];

// Guarded
protected $guarded = ['id', 'is_admin'];
```

### Create/Update'de

```php
// DOĞRU ✅
Product::create($request->only(['name', 'price', 'description']));

// DİKKATLİ ⚠️
Product::create($request->all()); // Sadece fillable tanımlıysa güvenli
```

---

## Security Review Checklist

- [ ] Hardcoded secret yok mu?
- [ ] Input validation yapılmış mı?
- [ ] Auth/permission check var mı?
- [ ] SQL injection koruması var mı?
- [ ] XSS koruması var mı?
- [ ] CSRF token kullanılmış mı?
- [ ] File upload güvenli mi?
- [ ] Mass assignment koruması var mı?
- [ ] Sensitive data log'lanmıyor mu?
- [ ] Dependency güvenli mi?

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [patch-policy.md](patch-policy.md)
- [reviewer.md](../skills/reviewer.md)
