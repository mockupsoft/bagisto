# Repo-Scout Skill

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.
> OpenAgents upstream referansı: `tools/openagents-upstream`

---

## Amaç

Repo içinde ilgili dosya ve pattern'leri bulur. Sadece keşif yapar, hiçbir değişiklik yapmaz veya önermez.

---

## Girdi

- Arama kriterleri (dosya türü, pattern, anahtar kelime)
- Arama kapsamı (tüm repo, belirli modül, belirli klasör)

---

## Çıktı

- İlgili dosya listesi (tam yollarla)
- Pattern analizi (benzer yapılar, tekrarlayan kodlar)
- Bağımlılık haritası (gerekirse)

### Çıktı Formatı

```markdown
## Repo-Scout Keşif Raporu

### Arama Kriterleri
[Kullanılan arama kriterleri]

### Bulunan Dosyalar
1. `path/to/file1.php` - [Kısa açıklama]
2. `path/to/file2.php` - [Kısa açıklama]
3. `path/to/file3.blade.php` - [Kısa açıklama]

### Pattern Analizi
[Tespit edilen pattern'ler]

### İlgili Bağımlılıklar
- [Bağımlılık 1]
- [Bağımlılık 2]

### Notlar
[Ek bilgiler]
```

---

## Kısıtlar

- **Sadece okuma:** Hiçbir dosyayı değiştirmez
- **Değişiklik yok:** Kod değişikliği önerisi yapmaz
- **Yorum/refactor önermez:** Sadece "nerede ne var" bilgisi verir
- **Sadece keşif:** Analiz yapar ama aksiyon önerisi vermez

---

## Dur ve Sor Koşulları

Aşağıdaki durumlarda **DURUR** ve netlik ister:

### Belirsizlik Durumları

- Arama kriterleri net değilse
- Çok fazla sonuç varsa (100+) ve filtreleme gerekiyorsa
- Birden fazla modül/alan eşleşiyorsa

### Kapsam Belirsizliği

- Hangi modülde aranacağı belirtilmemişse
- Admin/Shop ayrımı net değilse

---

## Allowed Tools

Bu skill yalnızca şu araçları kullanabilir:

- Dosya arama araçları (grep, find, search)
- Dosya okuma araçları (read)
- Dizin listeleme araçları (list)

**Kullanamaz:**
- Dosya yazma/düzenleme araçları
- Terminal komutları (değişiklik yapan)
- Git commit/push komutları

---

## Skill Separation Kuralları

| Kural | Açıklama |
|-------|----------|
| Repo-scout yorum/refactor önermez | ✅ Uyulmalı |
| Repo-scout sadece keşif yapar | ✅ Uyulmalı |
| Orchestrator dosya düzenlemez | Orchestrator'ın işi |
| Implementer plan yapmaz | Implementer'ın işi |
| Architect kod yazmaz | Architect'in işi |
| Reviewer kod düzeltmez | Reviewer'ın işi |
| Tester kod yazmaz | Tester'ın işi |

---

## Bagisto/Laravel Notları

### Modül Yapısı

```
packages/Webkul/
├── Admin/           # Admin panel
├── Shop/            # Storefront
├── Core/            # Core functionality
├── Checkout/        # Checkout process
├── Payment/         # Payment processing
├── Product/         # Product management
├── Category/        # Category management
├── Customer/        # Customer management
├── User/            # Admin users & ACL
├── Sales/           # Orders & invoices
└── ...
```

### Arama İpuçları

- **Controller'lar:** `*/Http/Controllers/`
- **Model'lar:** `*/Models/`
- **View'lar:** `*/Resources/views/`
- **Route'lar:** `*/Http/routes.php` veya `*/Routes/`
- **Migration'lar:** `*/Database/Migrations/`
- **Config:** `*/Config/`
- **Lang:** `*/Resources/lang/`

### Datagrid Pattern

Bagisto'da datagrid'ler için:
- `*/DataGrids/` klasörüne bak
- `Webkul\Ui\DataGrid\DataGrid` extend eden sınıflar

### Event/Listener Pattern

- Event'ler: `*/Events/`
- Listener'lar: `*/Listeners/`
- Service Provider'da kayıtlar

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [architect.md](architect.md)
- [laravel-bagisto-change-checklist.md](../checklists/laravel-bagisto-change-checklist.md)
