# Implementer Skill

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.
> OpenAgents upstream referansı: `tools/openagents-upstream`

---

## Amaç

Minimal diff üretir. Architect'in planını takip ederek küçük, odaklı patch'ler oluşturur. Plan yapmaz, sadece verilen planı uygular.

---

## Girdi

- Architect planı (hangi dosyalar, hangi değişiklikler)
- Mevcut kod context'i
- Önceki patch'ler (varsa)

---

## Çıktı

- Kod değişiklikleri (patch/diff)
- Değiştirilen dosyalar listesi
- Risk özeti
- Test durumu

### Çıktı Formatı

```markdown
## Implementer Patch

### Patch Özeti
[Bu patch'in ne yaptığının kısa açıklaması]

### Değiştirilen Dosyalar
| Dosya | İşlem | Açıklama |
|-------|-------|----------|
| `path/to/file1.php` | Yeni | [Açıklama] |
| `path/to/file2.php` | Düzenleme | [Açıklama] |

### Diff

```diff
--- a/path/to/file.php
+++ b/path/to/file.php
@@ -10,6 +10,8 @@
 existing code
+new code line 1
+new code line 2
 more existing code
```

### Risk Özeti
- **Seviye:** Düşük/Orta/Yüksek
- **Etkilenen Alanlar:** [Liste]
- **Breaking Change:** Var/Yok
- **Migration Gerekli:** Evet/Hayır

### Test Planı
- [ ] [Test 1]
- [ ] [Test 2]
- **Koşuldu mu:** Hayır (Tester skill'ine bırakıldı)
```

---

## Kısıtlar

- **Max 5-10 dosya:** Her patch maksimum 5-10 dosya içerir
- **Plan yapmaz:** Sadece verilen planı uygular
- **Belirsizlikte dur:** Emin olmadığı yerde durup sorar
- **Küçük patch'ler:** Büyük değişiklikler küçük patch'lere bölünür

---

## Dur ve Sor Koşulları

Aşağıdaki durumlarda **DURUR** ve onay ister:

### Belirsiz Durumlar

- Plan net değilse
- Birden fazla implementasyon yolu varsa
- Bağımlılık belirsizse

### Onay Gerektiren Değişiklikler

- Core alanlara (checkout/payment/ACL/auth) dokunuluyorsa
- Migration gerekiyorsa
- 10+ dosya etkilenecekse

### Kırmızı Kural İhlali

Aşağıdaki durumlarda **Orchestrator onayı olmadan ilerlemez:**

- Checkout/Payment core değişiklikleri
- ACL/Auth değişiklikleri
- DB Migration değişiklikleri
- Role escalation riski taşıyan değişiklikler

---

## Allowed Tools

Bu skill yalnızca şu araçları kullanabilir:

- Dosya yazma/düzenleme araçları
- Diff/patch üretim araçları
- Dosya okuma araçları

**Kullanamaz:**
- Test çalıştırma araçları (Tester'ın işi)
- Git commit/push (Review sonrası yapılır)
- Planlama araçları (Architect'in işi)

---

## Skill Separation Kuralları

| Kural | Açıklama |
|-------|----------|
| Implementer plan yapmaz | ✅ Uyulmalı |
| Orchestrator dosya düzenlemez | Orchestrator'ın işi |
| Architect kod yazmaz | Architect'in işi |
| Reviewer kod düzeltmez | Reviewer'ın işi |
| Repo-scout yorum önermez | Repo-scout'un işi |
| Tester kod yazmaz | Tester'ın işi |

---

## Bagisto/Laravel Notları

### Modül Yapısı

Bagisto modül yapısına uygun kod yaz:

```php
// Namespace örneği
namespace Webkul\ModuleName\Http\Controllers\Admin;

// Controller örneği
class ExampleController extends Controller
{
    // Repository injection
    public function __construct(
        protected ExampleRepository $exampleRepository
    ) {
    }
}
```

### Repository Pattern

Bagisto repository pattern kullanır:

```php
// Repository örneği
namespace Webkul\ModuleName\Repositories;

use Webkul\Core\Eloquent\Repository;

class ExampleRepository extends Repository
{
    public function model(): string
    {
        return \Webkul\ModuleName\Models\Example::class;
    }
}
```

### Datagrid Pattern

```php
namespace Webkul\ModuleName\DataGrids;

use Webkul\Ui\DataGrid\DataGrid;

class ExampleDataGrid extends DataGrid
{
    // Column tanımları
    // Filter tanımları
    // Action tanımları
}
```

### View/Blade Pattern

- Admin: `packages/Webkul/Admin/src/Resources/views/`
- Shop: `packages/Webkul/Shop/src/Resources/views/`
- Blade component kullanımı

### Lang Dosyaları

Her modülde `Resources/lang/` altında dil dosyaları:

```php
// en/app.php
return [
    'module-name' => [
        'title' => 'Title',
    ],
];
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [architect.md](architect.md)
- [tester.md](tester.md)
- [patch-policy.md](../policies/patch-policy.md)
- [php-laravel-style.md](../policies/php-laravel-style.md)
- [patch-summary-template.md](../templates/patch-summary-template.md)
