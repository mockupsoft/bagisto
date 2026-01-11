# Geliştirici Kurulum Rehberi

Bu doküman, `mockupsoft/bagisto` projesini sıfırdan çalışır hale getirmek için gereken adımları açıklar.

## Ön Gereksinimler

- PHP 8.2+
- Composer 2.x
- MySQL 8.x
- Node.js 18+ (opsiyonel, frontend build için)

## Hızlı Başlangıç

### 1. Bağımlılıkları Yükle

```bash
composer install
```

### 2. Environment Dosyasını Hazırla

```bash
cp .env.example .env
php artisan key:generate
```

`.env` dosyasında DB ayarlarını düzenle:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bagisto_dev
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Veritabanını Hazırla

```bash
php artisan migrate:fresh
php artisan db:seed --class=DevBagistoSeeder
```

### 4. Admin Panele Giriş

```
URL: http://localhost/admin
Email: admin@example.com
Password: admin123
```

---

## DevBagistoSeeder

`DevBagistoSeeder`, Bagisto admin panelinin çalışması için gereken minimum verileri ekler.

### Özellikler

- **İdempotent:** Birden fazla çalıştırılabilir, duplicate oluşturmaz
- **Güvenli:** Sadece `local`/`testing` ortamlarında çalışır
- **Yapılandırılabilir:** Environment variable'lar ile özelleştirilebilir

### Environment Variables

| Variable | Varsayılan | Açıklama |
|----------|------------|----------|
| `DEV_SEEDER_ENABLED` | `false` | Production'da seeder'ı zorla etkinleştir (önerilmez) |
| `DEV_ADMIN_EMAIL` | `admin@example.com` | Admin kullanıcı email adresi |
| `DEV_ADMIN_PASSWORD` | `admin123` | Admin kullanıcı şifresi (varsayılan kullanılırsa uyarı verir) |

### Özel Credentials ile Kullanım

```bash
# .env dosyasına ekle:
DEV_ADMIN_EMAIL=myemail@company.com
DEV_ADMIN_PASSWORD=my-secure-password-123

# Sonra:
php artisan db:seed --class=DevBagistoSeeder
```

veya tek seferlik:

```bash
DEV_ADMIN_EMAIL=myemail@company.com DEV_ADMIN_PASSWORD=secret php artisan db:seed --class=DevBagistoSeeder
```

### Seed Edilen Tablolar

| Tablo | Açıklama |
|-------|----------|
| `locales` | Dil ayarları (en) |
| `currencies` | Para birimi (USD) |
| `categories` | Root kategori |
| `category_translations` | Kategori çevirisi |
| `channels` | Default kanal |
| `channel_translations` | Kanal çevirisi |
| `channel_locales` | Kanal-Dil pivot |
| `channel_currencies` | Kanal-Para birimi pivot |
| `customer_groups` | Müşteri grupları (general, guest) |
| `roles` | Admin rolü (Administrator) |
| `admins` | Admin kullanıcı |

---

## MockupSoft/Companies Modülü

Bu proje, `MockupSoft/Companies` adlı örnek bir admin CRUD modülü içerir.

### Modül Yapısı

```
packages/MockupSoft/Companies/
├── src/
│   ├── Config/           # ACL ve Menu ayarları
│   ├── Contracts/        # Model contract interface
│   ├── Database/         # Migrations
│   ├── DataGrids/        # Admin DataGrid
│   ├── Http/Controllers/ # Admin controller
│   ├── Models/           # Eloquent model + Proxy
│   ├── Providers/        # Service providers
│   ├── Repositories/     # Repository pattern
│   ├── Resources/        # Views ve lang dosyaları
│   └── Routes/           # Admin routes
└── composer.json
```

### Admin URL

```
http://localhost/admin/mockupsoft/companies
```

### Route Listesi

```bash
php artisan route:list --name=mockupsoft
```

---

## Sık Karşılaşılan Sorunlar

### "getCurrentChannelCode(): Return value must be of type string, null returned"

**Sebep:** `channels` tablosu boş.

**Çözüm:**
```bash
php artisan db:seed --class=DevBagistoSeeder
```

### "Class not found" hataları

**Çözüm:**
```bash
composer dump-autoload
php artisan config:clear
```

### Migration hataları

**Çözüm:**
```bash
php artisan migrate:fresh
php artisan db:seed --class=DevBagistoSeeder
```

---

## Commit Geçmişi

| Patch | Commit | Açıklama |
|-------|--------|----------|
| Patch 0 | PSR-4 + Concord kaydı | Root composer.json ve config/concord.php |
| Patch 1 | Module skeleton | Providers, Model, Migration |
| Patch 2A | Admin CRUD | Routes, Controller, DataGrid, ACL, Menu, Views |
| Patch 2B | Contract + Proxy | Concord model binding |
| - | DevBagistoSeeder | Reproducible dev environment |
