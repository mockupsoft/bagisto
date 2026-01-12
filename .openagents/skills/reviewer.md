# Reviewer Skill

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.
> OpenAgents upstream referansı: `tools/openagents-upstream`

---

## Amaç

Diff review yapar, güvenlik/performans/geri uyumluluk notları çıkarır ve go/no-go kararı verir. Kod düzeltmez.

---

## Girdi

- Patch (Implementer'dan gelen diff)
- Test sonuçları (Tester'dan)
- Architect planı (referans için)

---

## Çıktı

- Review raporu
- Güvenlik/performans/geri uyumluluk notları
- Go/No-Go kararı
- Onay/Red gerekçesi

### Çıktı Formatı

```markdown
## Reviewer Raporu

### Review Özeti
| Kategori | Durum |
|----------|-------|
| Kod Kalitesi | ✅/⚠️/❌ |
| Güvenlik | ✅/⚠️/❌ |
| Performans | ✅/⚠️/❌ |
| Geri Uyumluluk | ✅/⚠️/❌ |
| Test Coverage | ✅/⚠️/❌ |

### Güvenlik Notları
- [Güvenlik notu 1]
- [Güvenlik notu 2]

### Performans Notları
- [Performans notu 1]

### Geri Uyumluluk Notları
- [Breaking change var mı?]
- [Deprecation gerekli mi?]

### Kod Kalitesi Notları
- [PSR-12 uyumu]
- [Laravel/Bagisto conventions]

### Karar

**GO / NO-GO**

### Gerekçe
[Kararın gerekçesi]

### Gerekli Aksiyonlar (No-Go ise)
1. [Aksiyon 1]
2. [Aksiyon 2]
```

---

## Kısıtlar

- **Kod düzeltmez:** Sadece review yapar, düzeltmeyi Implementer yapar
- **Güvenlik ve performans odaklı:** Bu alanlara özel dikkat
- **Objektif değerlendirme:** Kişisel tercih değil, standartlara göre

---

## Dur ve Sor Koşulları

Aşağıdaki durumlarda **DURUR** ve escalation yapar:

### Güvenlik Riskleri

- SQL injection riski
- XSS riski
- Auth bypass riski
- Sensitive data exposure
- CSRF koruması eksikliği

### Breaking Changes

- Public API değişikliği
- Database schema breaking change
- Config format değişikliği

### Performans Endişeleri

- N+1 query riski
- Büyük data set'lerde yavaşlık riski
- Memory leak riski

---

## Allowed Tools

Bu skill yalnızca şu araçları kullanabilir:

- Diff görüntüleme araçları
- Dosya okuma araçları
- Checklist araçları

**Kullanamaz:**
- Dosya yazma/düzenleme araçları
- Terminal komutları (değişiklik yapan)
- Git commit/push

---

## Skill Separation Kuralları

| Kural | Açıklama |
|-------|----------|
| Reviewer kod düzeltmez | ✅ Uyulmalı |
| Orchestrator dosya düzenlemez | Orchestrator'ın işi |
| Implementer plan yapmaz | Implementer'ın işi |
| Architect kod yazmaz | Architect'in işi |
| Repo-scout yorum önermez | Repo-scout'un işi |
| Tester kod yazmaz | Tester'ın işi |

---

## Review Checklist

### Güvenlik Kontrolü

- [ ] Input validation yapılmış mı?
- [ ] Auth/permission check var mı?
- [ ] SQL injection koruması var mı?
- [ ] XSS koruması var mı?
- [ ] CSRF token kullanılmış mı?
- [ ] Sensitive data log'lanmıyor mu?
- [ ] Hardcoded secret yok mu?

### Performans Kontrolü

- [ ] N+1 query riski var mı?
- [ ] Eager loading kullanılmış mı?
- [ ] Index kullanımı uygun mu?
- [ ] Cache kullanımı düşünülmüş mü?
- [ ] Büyük data set'ler için pagination var mı?

### Kod Kalitesi Kontrolü

- [ ] PSR-12 standartlarına uygun mu?
- [ ] Laravel conventions takip edilmiş mi?
- [ ] Bagisto patterns kullanılmış mı?
- [ ] DRY prensibi uygulanmış mı?
- [ ] Yeterli error handling var mı?

### Geri Uyumluluk Kontrolü

- [ ] Breaking change var mı?
- [ ] Deprecation warning gerekli mi?
- [ ] Migration rollback mümkün mü?

---

## Bagisto/Laravel Notları

### Güvenlik Kontrol Noktaları

```php
// Auth check
if (! auth()->guard('admin')->check()) { ... }

// Permission check
if (! bouncer()->hasPermission('catalog.products.edit')) { ... }

// Validation
$this->validate(request(), [...]);

// Mass assignment protection
protected $fillable = [...];
protected $guarded = [...];
```

### Performans Kontrol Noktaları

```php
// N+1 önleme - Eager loading
$products = Product::with('categories', 'images')->get();

// Chunking for large datasets
Product::chunk(100, function ($products) { ... });

// Cache kullanımı
Cache::remember('key', $ttl, function () { ... });
```

### Bagisto Özel Kontroller

- Repository pattern kullanımı
- Event/Listener kullanımı
- Datagrid implementation
- Translation dosyaları

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [tester.md](tester.md)
- [doc-writer.md](doc-writer.md)
- [security-policy.md](../policies/security-policy.md)
- [php-laravel-style.md](../policies/php-laravel-style.md)
- [review-template.md](../templates/review-template.md)
