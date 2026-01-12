# Review Şablonu

Bu şablon, Reviewer skill'inin review çıktısı için kullanılır.

---

## Kullanım

Reviewer skill'i review yaparken bu formatı kullanır.

---

# Review Raporu

**Review ID:** REVIEW-[YYYY-MM-DD]-[##]
**Tarih:** [YYYY-MM-DD]
**Reviewer:** Reviewer Skill

---

## 1. Review Bilgileri

### İncelenen Patch
[Patch ID veya referans]

### İlgili Plan
[Architect planına referans, varsa]

### Scope
- **Dosya Sayısı:** X
- **Satır Değişikliği:** +X / -X

---

## 2. Genel Değerlendirme

### Özet Tablo

| Kategori | Değerlendirme |
|----------|---------------|
| **Kod Kalitesi** | ✅ İyi / ⚠️ Düzeltme Gerekli / ❌ Sorunlu |
| **Güvenlik** | ✅ İyi / ⚠️ Düzeltme Gerekli / ❌ Sorunlu |
| **Performans** | ✅ İyi / ⚠️ Düzeltme Gerekli / ❌ Sorunlu |
| **Geri Uyumluluk** | ✅ İyi / ⚠️ Düzeltme Gerekli / ❌ Sorunlu |
| **Test Coverage** | ✅ Yeterli / ⚠️ Eksik / ❌ Yetersiz |

---

## 3. Kod Kalitesi

### PSR-12 Uyumu
- [ ] Indentation doğru
- [ ] Naming conventions uygun
- [ ] Line length uygun

### Laravel/Bagisto Conventions
- [ ] Controller conventions
- [ ] Repository pattern
- [ ] Blade conventions

### Notlar
[Kod kalitesi ile ilgili notlar]

---

## 4. Güvenlik Kontrolü

### Checklist

| Kontrol | Durum | Not |
|---------|-------|-----|
| Input validation | ✅ / ❌ | [Not] |
| Auth/permission check | ✅ / ❌ | [Not] |
| SQL injection koruması | ✅ / ❌ | [Not] |
| XSS koruması | ✅ / ❌ | [Not] |
| CSRF token | ✅ / ❌ | [Not] |
| Hardcoded secret | ✅ Yok / ❌ Var | [Not] |
| Sensitive data logging | ✅ Yok / ❌ Var | [Not] |

### Güvenlik Notları
[Güvenlik ile ilgili detaylı notlar]

---

## 5. Performans Kontrolü

### Checklist

| Kontrol | Durum | Not |
|---------|-------|-----|
| N+1 query riski | ✅ Yok / ❌ Var | [Not] |
| Eager loading kullanımı | ✅ / ❌ / N/A | [Not] |
| Index kullanımı | ✅ / ❌ / N/A | [Not] |
| Cache kullanımı | ✅ / ❌ / N/A | [Not] |
| Pagination | ✅ / ❌ / N/A | [Not] |

### Performans Notları
[Performans ile ilgili detaylı notlar]

---

## 6. Geri Uyumluluk

### Breaking Changes

| Değişiklik | Breaking? | Açıklama |
|------------|-----------|----------|
| [Değişiklik 1] | Evet / Hayır | [Açıklama] |

### Deprecation Gerekliliği
- [ ] Deprecation gerekli mi? Evet / Hayır
- [ ] Deprecation notice eklendi mi? Evet / Hayır / N/A

### Migration Kontrolü
- [ ] Migration reversible mi? Evet / Hayır / N/A
- [ ] Rollback test edildi mi? Evet / Hayır / N/A

---

## 7. Test Durumu

### Test Sonuçları
| Kategori | Sonuç |
|----------|-------|
| Unit Tests | ✅ X passed / ❌ Y failed |
| Feature Tests | ✅ X passed / ❌ Y failed |
| Manuel Test | ✅ / ❌ / ⏳ |

### Coverage
- [ ] Yeni kod için test var mı? Evet / Hayır
- [ ] Coverage yeterli mi? Evet / Hayır

---

## 8. Sorunlar ve Öneriler

### Kritik Sorunlar (Blocker)
| # | Sorun | Dosya | Satır | Açıklama |
|---|-------|-------|-------|----------|
| 1 | [Sorun] | `path/to/file.php` | L## | [Detay] |

### Önemli Sorunlar
| # | Sorun | Dosya | Satır | Açıklama |
|---|-------|-------|-------|----------|
| 1 | [Sorun] | `path/to/file.php` | L## | [Detay] |

### Öneriler (Opsiyonel)
| # | Öneri | Dosya | Açıklama |
|---|-------|-------|----------|
| 1 | [Öneri] | `path/to/file.php` | [Detay] |

---

## 9. Karar

### GO / NO-GO

# **[GO / NO-GO]**

---

### Gerekçe
[Kararın gerekçesi]

---

### Gerekli Aksiyonlar (NO-GO ise)

| # | Aksiyon | Öncelik | Sorumlu |
|---|---------|---------|---------|
| 1 | [Aksiyon] | Kritik / Önemli | Implementer |
| 2 | [Aksiyon] | Kritik / Önemli | Implementer |

---

## 10. Onay

- [ ] **Review Tamamlandı**
- [ ] **Karar Verildi:** GO / NO-GO
- [ ] **Merge'e Hazır** (GO ise)

---

*Bu review Reviewer skill tarafından oluşturulmuştur.*
