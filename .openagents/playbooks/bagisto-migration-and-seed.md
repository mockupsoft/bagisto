# Playbook: Migration ve Seeder

Bu playbook, Bagisto'da migration ve seeder oluşturma sürecini tanımlar.

---

## Genel Bakış

**Amaç:** Veritabanı değişikliklerini güvenli şekilde yönetmek

**Kritik Uyarı:**
> ⚠️ **DB Migration core değişiklikleri için Orchestrator'dan açık onay alınmadan Implementer patch üretmez.**

---

## Kırmızı Kural

> ⚠️ **Destructive migration'lar (column/table drop, data silme) için mutlaka Orchestrator onayı gerekir.**
>
> ⚠️ **Production veritabanını etkileyecek migration'lar özel dikkat gerektirir.**

---

## Skill Mapping

| Adım | Skill | Görev |
|------|-------|-------|
| 1 | Architect | Migration planı ve rollback stratejisi |
| 2 | Implementer | Migration ve seeder dosyalarını oluştur |
| 3 | Tester | Migration/rollback test et |
| 4 | Reviewer | Güvenlik ve performans review |

---

## Adım 1: Planlama (Architect)

**Skill:** Architect

**Görev:** Migration planı ve rollback stratejisi oluştur

### Plan İçeriği:

#### 1.1 Schema Değişiklikleri
- Yeni tablolar
- Yeni column'lar
- Index'ler
- Foreign key'ler

#### 1.2 Risk Analizi

| Risk | Seviye | Açıklama |
|------|--------|----------|
| Data loss | Düşük/Orta/Yüksek | [Açıklama] |
| Downtime | Düşük/Orta/Yüksek | [Açıklama] |
| Rollback | Kolay/Zor/İmkansız | [Açıklama] |

#### 1.3 Rollback Stratejisi
- `down()` method tanımı
- Data backup gerekliliği
- Rollback sonrası data durumu

### 🚦 Approval Gate 1
> **Migration planı Orchestrator tarafından onaylanır.**

---

## Adım 2: Implementasyon (Implementer)

**Skill:** Implementer

### Patch 2.1: Migration Dosyası

**Dosya:** `packages/Webkul/[Module]/src/Database/Migrations/[timestamp]_create_[table]_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            // Index'ler
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### Patch 2.2: Seeder Dosyası (Gerekirse)

**Dosya:** `packages/Webkul/[Module]/src/Database/Seeders/[Entity]Seeder.php`

```php
<?php

namespace Webkul\[Module]\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\[Module]\Models\[Entity];

class [Entity]Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        [Entity]::create([
            'name' => 'Default',
            'status' => true,
        ]);
    }
}
```

### Patch 2.3: Service Provider Güncelleme

```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
}
```

---

## Adım 3: Test (Tester)

**Skill:** Tester

### Test Senaryoları

#### 3.1 Migration Test
```bash
# Fresh migration
php artisan migrate

# Status kontrol
php artisan migrate:status
```

#### 3.2 Rollback Test
```bash
# Rollback
php artisan migrate:rollback --step=1

# Tekrar migrate
php artisan migrate
```

#### 3.3 Refresh Test (Dikkatli!)
```bash
# Sadece development'ta
php artisan migrate:refresh --seed
```

#### 3.4 Seeder Test
```bash
php artisan db:seed --class="Webkul\[Module]\Database\Seeders\[Entity]Seeder"
```

### Test Sonuç Formatı

```markdown
### Migration Test Sonuçları

| Test | Sonuç |
|------|-------|
| migrate | ✅/❌ |
| migrate:rollback | ✅/❌ |
| migrate (tekrar) | ✅/❌ |
| db:seed | ✅/❌ |

### Notlar
[Varsa hata detayları]
```

---

## Adım 4: Review (Reviewer)

**Skill:** Reviewer

### Review Checklist

#### Schema Review
- [ ] Column tipleri uygun mu?
- [ ] Nullable/default değerler doğru mu?
- [ ] Index'ler gerekli yerlerde mi?
- [ ] Foreign key'ler doğru mu?

#### Performance Review
- [ ] Büyük tablolarda column ekleme riski var mı?
- [ ] Index'ler yeterli mi?
- [ ] Full table scan riski var mı?

#### Rollback Review
- [ ] `down()` method doğru mu?
- [ ] Rollback data kaybına yol açar mı?
- [ ] Rollback test edildi mi?

#### Security Review
- [ ] Sensitive data encryption gerekli mi?
- [ ] PII (Personal Identifiable Information) koruması var mı?

### 🚦 Approval Gate 2
> **Reviewer GO/NO-GO kararı verir.**

---

## Migration Best Practices

### 1. Reversible Migration

```php
// DOĞRU ✅
public function down(): void
{
    Schema::dropIfExists('table_name');
}

// YANLIŞ ❌
public function down(): void
{
    // Boş
}
```

### 2. Column Ekleme (Mevcut Tablo)

```php
public function up(): void
{
    Schema::table('existing_table', function (Blueprint $table) {
        $table->string('new_column')->nullable()->after('existing_column');
    });
}

public function down(): void
{
    Schema::table('existing_table', function (Blueprint $table) {
        $table->dropColumn('new_column');
    });
}
```

### 3. Foreign Key

```php
public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->foreignId('customer_id')
              ->constrained('customers')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn('customer_id');
    });
}
```

### 4. Index Ekleme

```php
public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->index('sku');
        $table->index(['category_id', 'status']);
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropIndex(['sku']);
        $table->dropIndex(['category_id', 'status']);
    });
}
```

---

## Destructive Migration Uyarısı

Aşağıdaki işlemler için **özel format** kullanılmalıdır:

```markdown
⚠️ DESTRUCTIVE MIGRATION UYARISI

| Özellik | Değer |
|---------|-------|
| İşlem | Column drop / Table drop / Data delete |
| Etkilenen | [Tablo/column adı] |
| Data kaybı | Evet / Hayır |
| Geri alınabilir | Evet / Hayır |
| Backup gerekli | Evet / Hayır |

### Onay
- [ ] Orchestrator onayı alındı
- [ ] Backup alındı (gerekiyorsa)
- [ ] Rollback planı hazır
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [patch-policy.md](../policies/patch-policy.md)
- [laravel-bagisto-change-checklist.md](../checklists/laravel-bagisto-change-checklist.md)
