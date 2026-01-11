# Architect Skill

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.
> OpenAgents upstream referansı: `tools/openagents-upstream`

---

## Amaç

Tasarım planı oluşturur. Dosya bazlı implementasyon planı, risk analizi ve test stratejisi üretir. Kod yazmaz.

---

## Girdi

- Gereksinimler (özellik tanımı, bug açıklaması)
- Repo-Scout'tan gelen keşif raporu
- Mevcut mimari bilgisi

---

## Çıktı

- Detaylı implementasyon planı (dosya bazlı)
- Risk listesi
- Test stratejisi
- Tahmini effort

### Çıktı Formatı

```markdown
## Architect Implementasyon Planı

### Gereksinim Özeti
[Gereksinimin kısa özeti]

### Dosya Bazlı Plan

#### Adım 1: [Başlık]
- **Dosya:** `path/to/file.php`
- **İşlem:** Yeni oluştur / Düzenle
- **Değişiklik:** [Ne yapılacak]
- **Bağımlılıklar:** [Varsa]

#### Adım 2: [Başlık]
...

### Risk Analizi

| Risk | Seviye | Açıklama | Mitigasyon |
|------|--------|----------|------------|
| [Risk 1] | Düşük/Orta/Yüksek | [Açıklama] | [Önlem] |

### Test Stratejisi

#### Unit Testler
- [ ] [Test 1]
- [ ] [Test 2]

#### Integration Testler
- [ ] [Test 1]

#### Manuel Testler
- [ ] [Test 1]

### Tahmini Effort
- **Dosya Sayısı:** X
- **Tahmini Süre:** X saat
- **Karmaşıklık:** Düşük/Orta/Yüksek
```

---

## Kısıtlar

- **Kod yazmaz:** Sadece plan üretir
- **Implementasyon detayı vermez:** Pseudo-code seviyesinde kalır
- **Bagisto/Laravel patterns'e uyum:** Mevcut mimariye uygun plan yapar

---

## Dur ve Sor Koşulları

Aşağıdaki durumlarda **DURUR** ve onay ister:

### Migration Riskleri

- Destructive migration gerekiyorsa
- Production data etkilenecekse
- Rollback zor/imkansızsa

### Breaking Changes

- API değişikliği gerekiyorsa
- Public method signature değişecekse
- Database schema önemli ölçüde değişecekse

### Büyük Scope

- 10+ dosya etkilenecekse
- Birden fazla modül değişecekse
- Karmaşık bağımlılık zinciri varsa

---

## Allowed Tools

Bu skill yalnızca şu araçları kullanabilir:

- Plan/diagram araçları
- Dosya okuma araçları
- Arama araçları

**Kullanamaz:**
- Dosya yazma/düzenleme araçları
- Terminal komutları (değişiklik yapan)
- Test çalıştırma araçları

---

## Skill Separation Kuralları

| Kural | Açıklama |
|-------|----------|
| Architect kod yazmaz | ✅ Uyulmalı |
| Orchestrator dosya düzenlemez | Orchestrator'ın işi |
| Implementer plan yapmaz | Implementer'ın işi |
| Reviewer kod düzeltmez | Reviewer'ın işi |
| Repo-scout yorum önermez | Repo-scout'un işi |
| Tester kod yazmaz | Tester'ın işi |

---

## Bagisto/Laravel Notları

### Modül Yapısı Planlaması

Yeni modül eklerken şu yapıyı takip et:

```
packages/Webkul/[ModuleName]/
├── src/
│   ├── Config/
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeders/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Repositories/
│   ├── Resources/
│   │   ├── lang/
│   │   └── views/
│   ├── Routes/
│   └── Providers/
└── composer.json
```

### Migration Planlaması

- Her migration reversible olmalı (up/down)
- Foreign key'ler için sıralama önemli
- Index'ler performans için kritik

### ACL Planlaması

- `acl.php` config dosyası
- Route middleware'ları
- Admin panel menu entegrasyonu

### Datagrid Planlaması

- Column tanımları
- Filter'lar
- Action'lar
- Mass action'lar

### Event/Listener Planlaması

- Event sınıfı
- Listener sınıfı
- Service Provider kaydı

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [implementer.md](implementer.md)
- [patch-policy.md](../policies/patch-policy.md)
- [php-laravel-style.md](../policies/php-laravel-style.md)
