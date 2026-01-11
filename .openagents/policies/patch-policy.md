# Patch Politikası

Bu politika, tüm kod değişiklikleri için geçerli kurallardır.

---

## Temel Kurallar

### 1. Maksimum Dosya Sayısı

- **Kural:** Her patch maksimum **5-10 dosya** içermelidir
- **Neden:** Küçük patch'ler review'ı kolaylaştırır, hata riskini azaltır
- **İhlal durumunda:** Patch küçük parçalara bölünür

### 2. Core Alan Koruması

Aşağıdaki alanlarda değişiklik yapılmadan önce **Orchestrator'dan açık onay** alınmalıdır:

| Alan | Konum | Risk Seviyesi |
|------|-------|---------------|
| Checkout | `packages/Webkul/Checkout/` | 🔴 Yüksek |
| Payment | `packages/Webkul/Payment/` | 🔴 Yüksek |
| ACL/Auth | `packages/Webkul/User/` | 🔴 Yüksek |
| Sales | `packages/Webkul/Sales/` | 🟡 Orta |

**Onay olmadan bu alanlara dokunulmaz.**

### 3. Role Escalation Kontrolü

- **Kural:** Rol/yetki seviyelerini etkileyen her değişiklik Orchestrator onayı gerektirir
- **Örnekler:**
  - Yeni permission ekleme
  - Mevcut permission değiştirme
  - Role hierarchy değişikliği
  - Guard değişiklikleri

---

## Migration Kuralları

### Reversible Migration Zorunluluğu

- Her migration **reversible** olmalıdır (up/down method)
- `down()` method boş bırakılmamalıdır
- Rollback test edilmelidir

```php
// DOĞRU
public function up(): void
{
    Schema::create('table_name', function (Blueprint $table) {
        // ...
    });
}

public function down(): void
{
    Schema::dropIfExists('table_name');
}

// YANLIŞ
public function down(): void
{
    // Boş bırakılmış
}
```

### Destructive Migration Uyarısı

Aşağıdaki işlemler için **özel uyarı** gerekir:

- Column drop
- Table drop
- Data silme
- Foreign key değişiklikleri

**Format:**
```markdown
⚠️ DESTRUCTIVE MIGRATION UYARISI
- İşlem: [Drop column/table/etc.]
- Etkilenen: [Tablo/column adı]
- Data kaybı: Evet/Hayır
- Rollback: Mümkün/Mümkün değil
- Onay gerekli: Evet
```

---

## Test Zorunluluğu

### Her Patch Sonrası Test

- **Kural:** Her patch sonrası test çalıştırılmalıdır
- **Minimum:** İlgili unit/feature testler
- **İdeal:** Tüm test suite

### Test Sonuç Kaydı

Patch özeti şunları içermelidir:

- Çalıştırılan test sayısı
- Başarılı/başarısız test sayısı
- Fail eden test varsa detay

---

## Risk Özeti Zorunluluğu

Her patch için **kısa risk bölümü** yazılmalıdır:

```markdown
### Risk Özeti
- **Seviye:** Düşük/Orta/Yüksek
- **Etkilenen Alanlar:** [Liste]
- **Breaking Change:** Var/Yok
- **Migration:** Gerekli/Gerekli değil
- **Rollback:** Kolay/Zor/İmkansız
```

---

## Patch Bölme Stratejisi

Büyük değişiklikler için:

1. **Hazırlık Patch'i:** Yeni dosyalar, boş sınıflar, interface'ler
2. **Core Logic Patch'i:** Ana iş mantığı
3. **Integration Patch'i:** Mevcut sistemle entegrasyon
4. **Test Patch'i:** Test dosyaları
5. **Cleanup Patch'i:** Deprecated kod temizliği

---

## Onay Kapıları

### Zorunlu Onay Noktaları

| Durum | Onay Gerekli |
|-------|--------------|
| Core alan değişikliği | ✅ Orchestrator |
| 10+ dosya | ✅ Orchestrator |
| Migration | ✅ Architect + Orchestrator |
| Breaking change | ✅ Tüm skill'ler |
| Security-related | ✅ Reviewer + Orchestrator |

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [security-policy.md](security-policy.md)
- [implementer.md](../skills/implementer.md)
- [patch-summary-template.md](../templates/patch-summary-template.md)
