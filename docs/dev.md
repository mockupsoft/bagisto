# Geliştirici Kurulum Rehberi

Bu doküman, `mockupsoft/bagisto` projesini sıfırdan çalışır hale getirmek için gereken adımları açıklar.

---

## ⚡ Tek Satır Hızlı Kurulum

```bash
composer install && cp .env.example .env && php artisan key:generate && php artisan migrate:fresh && php artisan db:seed --class=DevBagistoSeeder
```

> **Not:** Önce `.env` dosyasındaki DB ayarlarını düzenlemeyi unutmayın!

---

## Ön Gereksinimler

- PHP 8.2+
- Composer 2.x
- MySQL 8.x
- Node.js 18+ (opsiyonel, frontend build için)

---

## Adım Adım Kurulum

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

## MySQL 8 Authentication Sorunu

MySQL 8.x varsayılan olarak `caching_sha2_password` kullanır. Bazı PHP/PDO sürümleri bu ile uyumsuz olabilir.

### Belirtiler

```
SQLSTATE[HY000] [1524] Plugin 'mysql_native_password' is not loaded
```

veya

```
SQLSTATE[HY000] [2054] The server requested authentication method unknown to the client
```

### Çözüm 1: MySQL User Auth Method Değiştir

```sql
-- MySQL'e bağlan
mysql -u root -p

-- Auth method'u değiştir
ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY '';
FLUSH PRIVILEGES;
```

### Çözüm 2: my.cnf / my.ini Ayarı

```ini
[mysqld]
default_authentication_plugin=caching_sha2_password
```

### Laragon Özel Not

Laragon kullanıyorsanız ve MySQL 8.x yüklediyseniz:
- `C:\laragon\bin\mysql\mysql-8.x.x\my.ini` dosyasını kontrol edin
- PHP'nin mysqlnd extension'ının güncel olduğundan emin olun

---

## DevBagistoSeeder

`DevBagistoSeeder`, Bagisto admin panelinin çalışması için gereken minimum verileri ekler.

### Özellikler

- **İdempotent:** Birden fazla çalıştırılabilir, duplicate oluşturmaz
- **Güvenli:** Sadece `local`/`testing` ortamlarında çalışır
- **Çift Kilit:** Production override için 2 flag gerekir
- **Yapılandırılabilir:** Environment variable'lar ile özelleştirilebilir

### Environment Variables

| Variable | Varsayılan | Açıklama |
|----------|------------|----------|
| `DEV_SEEDER_ENABLED` | `false` | Non-local seeding için 1. flag |
| `DEV_SEEDER_I_KNOW_WHAT_I_AM_DOING` | `false` | Non-local seeding için 2. flag (çift kilit) |
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

### Production Override (ÖNERİLMEZ)

Non-local ortamda çalıştırmak için **her iki flag** gereklidir:

```bash
DEV_SEEDER_ENABLED=true DEV_SEEDER_I_KNOW_WHAT_I_AM_DOING=true php artisan db:seed --class=DevBagistoSeeder --force
```

> ⚠️ **Uyarı:** Bu sadece staging/demo ortamlar için kullanılmalıdır. Production'da asla kullanmayın!

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

---

## Smoke Test Komutları

Kurulum sonrası her şeyin çalıştığını doğrulamak için:

### 1. Route Kontrolü

```bash
php artisan route:list --name=mockupsoft
```

Beklenen çıktı: 5 route (index, show, store, update, delete)

### 2. Config Merge Kontrolü

```bash
php artisan tinker --execute="dd(array_keys(array_filter(config('acl'), fn(\$i)=>str_starts_with(\$i['key'],'mockupsoft'))));"
```

Beklenen: `mockupsoft.companies.*` ACL keys

### 3. Model Binding Kontrolü

```bash
php artisan tinker --execute="dd(app(\MockupSoft\Companies\Contracts\Company::class) instanceof \MockupSoft\Companies\Models\Company);"
```

Beklenen: `true`

### 4. Repository Kontrolü

```bash
php artisan tinker --execute="dd(app(\MockupSoft\Companies\Repositories\CompanyRepository::class)->model());"
```

Beklenen: `MockupSoft\Companies\Contracts\Company`

### 5. Company Create Testi

```bash
php artisan tinker --execute="
\$repo = app(\MockupSoft\Companies\Repositories\CompanyRepository::class);
\$company = \$repo->create(['name' => 'Test Co', 'email' => 'test@test.com', 'phone' => '555-1234', 'address' => 'Test St']);
dd(\$company->toArray());
"
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

### MySQL auth plugin hatası

**Çözüm:** Yukarıdaki "MySQL 8 Authentication Sorunu" bölümüne bakın.

---

## Commit Geçmişi

| Patch | Commit | Açıklama |
|-------|--------|----------|
| Patch 0 | PSR-4 + Concord kaydı | Root composer.json ve config/concord.php |
| Patch 1 | SaaS tenant/domain/db modeli | tenants/domains/tenant_databases global DB, primary domain domains.is_primary; resolver/ACL/DB switch yok |

> Patch-1 onaylandı: Global DB’de tenants/domains/tenant_databases modeli kuruldu. Primary domain yalnızca domains.is_primary üzerinden yönetilecek. Resolver/DB switch/ACL gibi kırmızı alanlara dokunulmadı.
| Patch 1 | Module skeleton | Providers, Model, Migration |
| Patch 2A | Admin CRUD | Routes, Controller, DataGrid, ACL, Menu, Views |
| Patch 2B | Contract + Proxy | Concord model binding |
| - | DevBagistoSeeder | Reproducible dev environment |
| - | Seeder polish | Çift kilit guard + docs |

---

## Versiyon

Bu dokümantasyon `v0.1.0-dev-seeded` milestone'u ile günceldir.
