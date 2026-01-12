# Patch Özeti Şablonu

Bu şablon, Implementer skill'inin patch çıktısı için kullanılır.

---

## Kullanım

Implementer skill'i patch oluştururken bu formatı kullanır.

---

# Patch Özeti

**Patch ID:** PATCH-[YYYY-MM-DD]-[##]
**Tarih:** [YYYY-MM-DD]
**Oluşturan:** Implementer Skill

---

## 1. Patch Bilgileri

### Özet
[Bu patch'in ne yaptığının tek cümlelik özeti]

### İlgili Plan
[Architect planına referans, varsa]

### İlgili Bug/Issue
[Ticket/issue numarası, varsa]

---

## 2. Değiştirilen Dosyalar

| # | Dosya | İşlem | Açıklama |
|---|-------|-------|----------|
| 1 | `path/to/file1.php` | Yeni | [Açıklama] |
| 2 | `path/to/file2.php` | Düzenleme | [Açıklama] |
| 3 | `path/to/file3.php` | Silme | [Açıklama] |

**Toplam:** X dosya

---

## 3. Diff

### `path/to/file1.php` (Yeni)

```php
<?php
// Yeni dosya içeriği
```

---

### `path/to/file2.php` (Düzenleme)

```diff
--- a/path/to/file2.php
+++ b/path/to/file2.php
@@ -10,6 +10,8 @@
 // existing code
+// new code line 1
+// new code line 2
 // more existing code
```

---

## 4. Risk Özeti

### Risk Seviyesi

| Kategori | Seviye |
|----------|--------|
| Genel Risk | 🟢 Düşük / 🟡 Orta / 🔴 Yüksek |
| Breaking Change | Var / Yok |
| Migration | Gerekli / Gerekli Değil |
| Rollback | Kolay / Zor / İmkansız |

### Etkilenen Alanlar
- [Alan 1]
- [Alan 2]

### Potansiyel Sorunlar
- [Potansiyel sorun 1, varsa]
- [Potansiyel sorun 2, varsa]

---

## 5. Bağımlılıklar

### Bu Patch'in Bağımlılıkları
- [Önceki patch, varsa]
- [Başka bağımlılık, varsa]

### Bu Patch'e Bağımlı Olanlar
- [Sonraki patch, varsa]

---

## 6. Test Planı

### Gerekli Testler
- [ ] [Test 1]
- [ ] [Test 2]
- [ ] [Test 3]

### Test Koşuldu mu?
**Hayır** - Tester skill'ine bırakıldı

### Manuel Test Adımları
1. [Adım 1]
2. [Adım 2]
3. [Beklenen sonuç]

---

## 7. Checklist Kontrolü

| Kontrol | Durum |
|---------|-------|
| Max 5-10 dosya | ✅ / ❌ |
| Risk özeti yazıldı | ✅ / ❌ |
| Diff eklendi | ✅ / ❌ |
| Test planı tanımlandı | ✅ / ❌ |
| Core alan onayı (gerekiyorsa) | ✅ / ❌ / N/A |

---

## 8. Core Alan Kontrolü

### Kırmızı Kural

| Alan | Dokunuldu mu? | Onay Alındı mı? |
|------|---------------|-----------------|
| Checkout | Evet / Hayır | ✅ / ❌ / N/A |
| Payment | Evet / Hayır | ✅ / ❌ / N/A |
| ACL/Auth | Evet / Hayır | ✅ / ❌ / N/A |
| DB Migration | Evet / Hayır | ✅ / ❌ / N/A |
| Role Escalation | Evet / Hayır | ✅ / ❌ / N/A |

---

## 9. Notlar

[Ek notlar, dikkat edilmesi gerekenler, sonraki adımlar]

---

## Sonraki Adım

**Tester:** Bu patch'i test et

---

*Bu patch Implementer skill tarafından oluşturulmuştur.*
