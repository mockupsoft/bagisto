# Plan Şablonu

Bu şablon, Architect skill'inin plan çıktısı için kullanılır.

---

## Kullanım

Architect skill'i plan oluştururken bu formatı kullanır.

---

# [Görev Başlığı] - Implementasyon Planı

**Tarih:** [YYYY-MM-DD]
**Oluşturan:** Architect Skill
**Durum:** Taslak / Onay Bekliyor / Onaylandı

---

## 1. Gereksinim Özeti

### Talep
[Kullanıcı talebinin kısa özeti]

### Beklenen Çıktı
[Ne oluşturulacak/değiştirilecek]

### Kapsam
- **Modüller:** [Etkilenen modüller]
- **Dosya Sayısı:** ~X dosya
- **Karmaşıklık:** Düşük / Orta / Yüksek

---

## 2. Dosya Bazlı Plan

### Patch 1: [Başlık]

| # | Dosya | İşlem | Açıklama |
|---|-------|-------|----------|
| 1 | `path/to/file1.php` | Yeni | [Ne oluşturulacak] |
| 2 | `path/to/file2.php` | Düzenleme | [Ne değişecek] |

**Bağımlılıklar:** [Varsa]

---

### Patch 2: [Başlık]

| # | Dosya | İşlem | Açıklama |
|---|-------|-------|----------|
| 1 | `path/to/file3.php` | Yeni | [Ne oluşturulacak] |

**Bağımlılıklar:** Patch 1 tamamlanmalı

---

## 3. Risk Analizi

### Risk Matrisi

| Risk | Olasılık | Etki | Seviye | Mitigasyon |
|------|----------|------|--------|------------|
| [Risk 1] | Düşük/Orta/Yüksek | Düşük/Orta/Yüksek | 🟢/🟡/🔴 | [Önlem] |
| [Risk 2] | Düşük/Orta/Yüksek | Düşük/Orta/Yüksek | 🟢/🟡/🔴 | [Önlem] |

### Breaking Changes
- [ ] Breaking change var mı? Evet / Hayır
- [ ] Deprecation gerekli mi? Evet / Hayır

### Rollback Stratejisi
[Rollback nasıl yapılır]

---

## 4. Core Alan Kontrolü

### Kırmızı Kural Kontrolü

| Alan | Dokunuluyor mu? | Onay Gerekli mi? |
|------|-----------------|------------------|
| Checkout | Evet / Hayır | ✅ / - |
| Payment | Evet / Hayır | ✅ / - |
| ACL/Auth | Evet / Hayır | ✅ / - |
| DB Migration | Evet / Hayır | ✅ / - |
| Role Escalation | Evet / Hayır | ✅ / - |

**Orchestrator Onayı Gerekli:** Evet / Hayır

---

## 5. Test Stratejisi

### Unit Testler
- [ ] [Test 1 açıklaması]
- [ ] [Test 2 açıklaması]

### Feature Testler
- [ ] [Test 1 açıklaması]

### Manuel Testler
- [ ] [Test 1 açıklaması]

### Regression Testler
- [ ] [İlgili mevcut testler]

---

## 6. Onay Kapıları

| # | Nokta | Skill | Durum |
|---|-------|-------|-------|
| 1 | Plan onayı | Orchestrator | ⏳ Bekliyor |
| 2 | [Core alan değişikliği] | Orchestrator | ⏳ Bekliyor |
| 3 | Review | Reviewer | ⏳ Bekliyor |

---

## 7. Tahmini Effort

| Metrik | Değer |
|--------|-------|
| Toplam Dosya | X |
| Tahmini Süre | X saat |
| Patch Sayısı | X |
| Karmaşıklık | Düşük / Orta / Yüksek |

---

## 8. Notlar

[Ek notlar, dikkat edilmesi gerekenler]

---

## Onay

- [ ] **Orchestrator Onayı:** [Tarih]
- [ ] **Implementasyona Hazır**

---

*Bu plan Architect skill tarafından oluşturulmuştur.*
