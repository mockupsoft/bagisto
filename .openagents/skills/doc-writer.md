# Doc-Writer Skill

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.
> OpenAgents upstream referansı: `tools/openagents-upstream`

---

## Amaç

Release notes, PR açıklaması ve changelog girişleri oluşturur. Teknik değişiklikleri anlaşılır dokümantasyona çevirir.

---

## Girdi

- Patch (Implementer'dan)
- Review sonuçları (Reviewer'dan)
- Test sonuçları (Tester'dan)
- Architect planı (referans)

---

## Çıktı

- Release notes
- PR açıklaması
- Changelog girişi
- Kullanıcı dokümantasyonu (gerekirse)

### Çıktı Formatı

```markdown
## Doc-Writer Çıktısı

### PR Açıklaması

#### Özet
[Değişikliğin kısa özeti]

#### Değişiklikler
- [Değişiklik 1]
- [Değişiklik 2]

#### Test Sonuçları
- ✅ Unit testler geçti
- ✅ Feature testler geçti

#### Breaking Changes
[Varsa listele, yoksa "Yok"]

#### Ekran Görüntüleri
[Gerekirse]

---

### Changelog Girişi

#### [Versiyon] - [Tarih]

##### Eklenenler
- [Yeni özellik]

##### Değişenler
- [Değişiklik]

##### Düzeltilenler
- [Bug fix]

##### Kaldırılanlar
- [Kaldırılan özellik]

---

### Release Notes

#### [Versiyon] Sürüm Notları

##### Yenilikler
[Kullanıcı odaklı açıklama]

##### İyileştirmeler
[Performans/UX iyileştirmeleri]

##### Düzeltmeler
[Bug fix'ler]

##### Notlar
[Önemli notlar, migration gereksinimleri vb.]
```

---

## Kısıtlar

- **Türkçe yazım:** Tüm dokümantasyon Türkçe
- **Net ve özlü:** Gereksiz detay yok
- **Kullanıcı odaklı:** Teknik jargon minimumda

---

## Dur ve Sor Koşulları

Aşağıdaki durumlarda **DURUR** ve netlik ister:

### Belirsiz Değişiklikler

- Değişikliğin kullanıcı etkisi net değilse
- Breaking change seviyesi belirsizse

### Eksik Bilgi

- Test sonuçları eksikse
- Review sonuçları eksikse

---

## Allowed Tools

Bu skill yalnızca şu araçları kullanabilir:

- Dokümantasyon oluşturma araçları
- Dosya okuma araçları
- Markdown düzenleme araçları

**Kullanamaz:**
- Kod düzenleme araçları
- Test çalıştırma araçları
- Git commit/push (yalnızca doc dosyaları için izinli)

---

## Skill Separation Kuralları

| Kural | Açıklama |
|-------|----------|
| Doc-writer sadece dokümantasyon yazar | ✅ Uyulmalı |
| Orchestrator dosya düzenlemez | Orchestrator'ın işi |
| Implementer plan yapmaz | Implementer'ın işi |
| Architect kod yazmaz | Architect'in işi |
| Reviewer kod düzeltmez | Reviewer'ın işi |
| Repo-scout yorum önermez | Repo-scout'un işi |
| Tester kod yazmaz | Tester'ın işi |

---

## Yazım Kuralları

### Türkçe Yazım

- Türk Dil Kurumu kurallarına uygun
- Teknik terimler için kabul görmüş Türkçe karşılıklar
- Karşılığı olmayan terimler orijinal haliyle (italik)

### Ton ve Stil

- Profesyonel ve net
- Aktif cümleler tercih edilmeli
- Kısa paragraflar
- Bullet point kullanımı

### Changelog Formatı

Keep a Changelog formatına uygun:
- **Eklenenler:** Yeni özellikler
- **Değişenler:** Mevcut işlevsellikte değişiklikler
- **Deprecated:** Gelecekte kaldırılacak özellikler
- **Kaldırılanlar:** Kaldırılan özellikler
- **Düzeltilenler:** Bug fix'ler
- **Güvenlik:** Güvenlik düzeltmeleri

### PR Açıklama Formatı

- Özet (1-2 cümle)
- Değişiklikler listesi
- Test durumu
- Breaking changes (varsa)
- İlgili issue/ticket referansı

---

## Bagisto/Laravel Notları

### Versiyon Numaralama

Semantic Versioning (SemVer):
- **MAJOR:** Breaking changes
- **MINOR:** Yeni özellikler (geri uyumlu)
- **PATCH:** Bug fix'ler (geri uyumlu)

### Migration Notları

Migration içeren değişikliklerde:
- Rollback talimatları
- Data migration gereksinimleri
- Backup önerileri

### Modül Dokümantasyonu

Yeni modül eklendiğinde:
- Kurulum talimatları
- Konfigürasyon seçenekleri
- Kullanım örnekleri

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [reviewer.md](reviewer.md)
- [pr-description-template.md](../templates/pr-description-template.md)
