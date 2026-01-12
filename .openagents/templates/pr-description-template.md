# PR Açıklaması Şablonu

Bu şablon, Doc-Writer skill'inin PR açıklaması çıktısı için kullanılır.

---

## Kullanım

Doc-Writer skill'i PR açıklaması oluştururken bu formatı kullanır.

---

# [PR Başlığı]

## Özet

[Değişikliğin 1-2 cümlelik özeti]

## İlgili Issue/Ticket

- Fixes #[issue-number]
- Closes #[issue-number]
- Related to #[issue-number]

---

## Değişiklik Türü

- [ ] 🐛 Bug fix (breaking change olmayan hata düzeltmesi)
- [ ] ✨ Yeni özellik (breaking change olmayan yeni işlevsellik)
- [ ] 💥 Breaking change (mevcut işlevselliği etkileyen değişiklik)
- [ ] 📚 Dokümantasyon güncelleme
- [ ] 🔧 Refactoring (işlevsel değişiklik olmayan kod iyileştirmesi)
- [ ] ⚡ Performans iyileştirmesi
- [ ] 🔒 Güvenlik düzeltmesi

---

## Değişiklikler

### Eklenenler
- [Yeni özellik 1]
- [Yeni özellik 2]

### Değişenler
- [Değişiklik 1]
- [Değişiklik 2]

### Düzeltilenler
- [Bug fix 1]
- [Bug fix 2]

### Kaldırılanlar
- [Kaldırılan özellik, varsa]

---

## Teknik Detaylar

### Etkilenen Modüller
- `packages/Webkul/[Module1]`
- `packages/Webkul/[Module2]`

### Değiştirilen Dosyalar
<details>
<summary>Dosya listesi (X dosya)</summary>

- `path/to/file1.php`
- `path/to/file2.php`
- `path/to/file3.blade.php`

</details>

### Migration
- [ ] Migration içeriyor
- Migration dosyası: `[migration-file-name]`
- Rollback: Mümkün / Dikkatli

---

## Test Sonuçları

### Otomatik Testler
- ✅ Unit Tests: X passed
- ✅ Feature Tests: X passed
- ⏳ CI Pipeline: [Status]

### Manuel Test
<details>
<summary>Test adımları</summary>

1. [Adım 1]
2. [Adım 2]
3. [Beklenen sonuç]

</details>

---

## Ekran Görüntüleri

<details>
<summary>Ekran görüntüleri (varsa)</summary>

### Önce
[Screenshot veya "N/A"]

### Sonra
[Screenshot veya "N/A"]

</details>

---

## Breaking Changes

### ⚠️ Breaking Change Var mı?

**[ Evet / Hayır ]**

<details>
<summary>Breaking change detayları (varsa)</summary>

### Etkilenen
- [Etkilenen API/özellik]

### Migration Rehberi
```php
// Eski kullanım
$old = OldClass::method();

// Yeni kullanım
$new = NewClass::method();
```

### Deprecation Notice
[Deprecation timeline, varsa]

</details>

---

## Checklist

### Geliştirici Checklist
- [ ] Kod PSR-12 standartlarına uygun
- [ ] Yeni ve mevcut unit testler geçiyor
- [ ] Breaking change varsa dokümante edildi
- [ ] Self-review yapıldı

### Güvenlik Checklist
- [ ] Input validation yapıldı
- [ ] Auth/permission kontrolleri var
- [ ] Hardcoded secret yok
- [ ] XSS/SQL injection koruması var

### Bagisto Checklist
- [ ] ACL güncellemesi yapıldı (gerekiyorsa)
- [ ] Lang dosyaları güncellendi
- [ ] Migration reversible

---

## Reviewer Notları

[Reviewer'lar için özel notlar, dikkat edilmesi gerekenler]

---

## Deploy Notları

<details>
<summary>Deploy sonrası yapılacaklar (varsa)</summary>

- [ ] `php artisan migrate`
- [ ] `php artisan cache:clear`
- [ ] `php artisan config:clear`
- [ ] [Diğer]

</details>

---

## İlgili PR'lar

- [İlgili PR, varsa]

---

*Bu PR açıklaması Doc-Writer skill tarafından oluşturulmuştur.*
