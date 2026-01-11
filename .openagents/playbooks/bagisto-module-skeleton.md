# Playbook: Yeni Modül Oluşturma

Bu playbook, Bagisto için yeni bir modül/package oluşturma sürecini tanımlar.

---

## Genel Bakış

**Amaç:** Bagisto stilinde yeni bir modül oluşturmak

**Modül Yapısı:**
```
packages/Webkul/[ModuleName]/
├── src/
│   ├── Config/
│   ├── Database/
│   ├── Http/
│   ├── Models/
│   ├── Repositories/
│   ├── Resources/
│   ├── Routes/
│   └── Providers/
└── composer.json
```

---

## Skill Mapping

| Adım | Skill | Görev |
|------|-------|-------|
| 1 | Architect | Modül yapısını planla |
| 2 | Implementer | Skeleton dosyalarını oluştur |
| 3 | Tester | Modül yüklenebilirliğini test et |
| 4 | Doc-Writer | Modül dokümantasyonu |

---

## Adım 1: Planlama (Architect)

**Skill:** Architect

**Görev:** Modül yapısını ve bağımlılıkları planla

**Plan İçeriği:**

### 1.1 Modül Bilgileri
- Modül adı
- Namespace
- Bağımlılıklar

### 1.2 Dosya Yapısı
Oluşturulacak dosyalar listesi

### 1.3 Service Provider
- Boot method içeriği
- Register method içeriği

### 🚦 Approval Gate 1
> **Architect planı tamamlandığında Orchestrator onayı alınır.**

---

## Adım 2: Implementasyon (Implementer)

**Skill:** Implementer

### Patch 2.1: Temel Yapı

```
packages/Webkul/[ModuleName]/
├── src/
│   └── Providers/
│       └── [ModuleName]ServiceProvider.php
└── composer.json
```

### Patch 2.2: Config Dosyaları

```
src/Config/
├── acl.php
├── admin-menu.php
└── system.php (opsiyonel)
```

### Patch 2.3: Database Yapısı

```
src/Database/
├── Migrations/
└── Seeders/
```

### Patch 2.4: HTTP Yapısı

```
src/Http/
├── Controllers/
│   ├── Admin/
│   └── Shop/
├── Middleware/
└── Requests/
```

### Patch 2.5: Model ve Repository

```
src/Models/
src/Repositories/
```

### Patch 2.6: Resources

```
src/Resources/
├── lang/
│   ├── en/
│   │   └── app.php
│   └── tr/
│       └── app.php
└── views/
    ├── admin/
    └── shop/
```

### Patch 2.7: Routes

```
src/Routes/
├── admin-routes.php
└── shop-routes.php
```

---

## Adım 3: Test (Tester)

**Skill:** Tester

**Test Listesi:**
- [ ] Composer autoload çalışıyor mu?
- [ ] Service Provider yüklenebiliyor mu?
- [ ] Route'lar register ediliyor mu?
- [ ] View'lar bulunabiliyor mu?
- [ ] Lang dosyaları yüklenebiliyor mu?

**Komutlar:**
```bash
composer dump-autoload
php artisan package:discover
php artisan route:list | grep [modulename]
```

---

## Dosya Şablonları

### composer.json

```json
{
    "name": "webkul/[module-name]",
    "description": "Module description",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Author Name",
            "email": "author@example.com"
        }
    ],
    "require": {},
    "autoload": {
        "psr-4": {
            "Webkul\\[ModuleName]\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Webkul\\[ModuleName]\\Providers\\[ModuleName]ServiceProvider"
            ]
        }
    }
}
```

### Service Provider

```php
<?php

namespace Webkul\[ModuleName]\Providers;

use Illuminate\Support\ServiceProvider;

class [ModuleName]ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');
        
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', '[modulename]');
        
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', '[modulename]');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php', 'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/admin-menu.php', 'menu.admin'
        );
    }
}
```

### ACL Config

```php
<?php

return [
    [
        'key'   => '[modulename]',
        'name'  => '[modulename]::app.acl.title',
        'route' => 'admin.[modulename].index',
        'sort'  => 10,
    ],
];
```

### Admin Menu Config

```php
<?php

return [
    [
        'key'   => '[modulename]',
        'name'  => '[modulename]::app.menu.title',
        'route' => 'admin.[modulename].index',
        'sort'  => 10,
        'icon'  => 'icon-settings',
    ],
];
```

### Admin Routes

```php
<?php

use Illuminate\Support\Facades\Route;
use Webkul\[ModuleName]\Http\Controllers\Admin\[Entity]Controller;

Route::group([
    'prefix' => config('app.admin_url'),
    'middleware' => ['web', 'admin'],
], function () {
    Route::prefix('[modulename]')->group(function () {
        Route::controller([Entity]Controller::class)->group(function () {
            Route::get('', 'index')->name('admin.[modulename].index');
            // Diğer route'lar
        });
    });
});
```

### Lang File (en/app.php)

```php
<?php

return [
    'acl' => [
        'title' => 'Module Name',
    ],
    'menu' => [
        'title' => 'Module Name',
    ],
];
```

---

## Ana composer.json Güncelleme

Ana `composer.json` dosyasına repository ekleme:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/Webkul/*"
        }
    ],
    "require": {
        "webkul/[module-name]": "*"
    }
}
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [php-laravel-style.md](../policies/php-laravel-style.md)
- [bagisto-admin-crud.md](bagisto-admin-crud.md)
