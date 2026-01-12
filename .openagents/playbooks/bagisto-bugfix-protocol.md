# Playbook: Bug Fix Protokolü

Bu playbook, Bagisto'da bug fix sürecini tanımlar.

---

## Genel Bakış

**Amaç:** Bug'ları sistematik şekilde tespit edip düzeltmek

**Akış:**
```
Reproduce → Isolate → Patch Small → Test → Review
```

---

## Kırmızı Kural

> ⚠️ **Checkout/Payment core bug fix'leri için Orchestrator'dan açık onay alınmadan Implementer patch üretmez.**
>
> ⚠️ **Security-related bug'lar için Reviewer mutlaka dahil edilir.**

---

## Skill Mapping

| Adım | Skill | Görev |
|------|-------|-------|
| 1 | Repo-Scout | Bug'ı reproduce et ve ilgili kodu bul |
| 2 | Architect | Root cause analizi ve fix planı |
| 3 | Implementer | Minimal fix patch'i |
| 4 | Tester | Regression test |
| 5 | Reviewer | Fix review ve go/no-go |

---

## Adım 1: Reproduce ve Keşif (Repo-Scout)

**Skill:** Repo-Scout

### 1.1 Bug Reproduce

**Görev:** Bug'ı reproduce et

**Format:**
```markdown
### Bug Reproduce Raporu

**Bug ID:** [Ticket/Issue numarası]

**Adımlar:**
1. [Adım 1]
2. [Adım 2]
3. [Adım 3]

**Beklenen Davranış:**
[Olması gereken]

**Gerçekleşen Davranış:**
[Olan]

**Reproduce Edildi mi:** Evet / Hayır
```

### 1.2 İlgili Kod Keşfi

**Görev:** Bug'la ilgili dosyaları bul

**Çıktı:**
```markdown
### İlgili Dosyalar

| Dosya | İlişki |
|-------|--------|
| `path/to/file1.php` | [Açıklama] |
| `path/to/file2.php` | [Açıklama] |

### Stack Trace (varsa)
[Stack trace]

### Log Entries (varsa)
[İlgili log satırları]
```

---

## Adım 2: Analiz ve Planlama (Architect)

**Skill:** Architect

### 2.1 Root Cause Analizi

**Format:**
```markdown
### Root Cause Analizi

**Bug Türü:**
- [ ] Logic error
- [ ] Validation eksikliği
- [ ] Race condition
- [ ] Data integrity
- [ ] Configuration issue
- [ ] Diğer: [Açıklama]

**Root Cause:**
[Detaylı açıklama]

**Etkilenen Alanlar:**
- [Alan 1]
- [Alan 2]
```

### 2.2 Fix Planı

**Format:**
```markdown
### Fix Planı

**Önerilen Çözüm:**
[Çözüm açıklaması]

**Değiştirilecek Dosyalar:**
| Dosya | Değişiklik |
|-------|------------|
| `path/to/file.php` | [Ne değişecek] |

**Risk Analizi:**
- **Seviye:** Düşük / Orta / Yüksek
- **Regression Riski:** [Açıklama]
- **Side Effect:** [Varsa]

**Test Planı:**
- [ ] [Test 1]
- [ ] [Test 2]
```

### 🚦 Approval Gate 1
> **Core alan fix'i ise Orchestrator onayı alınır.**

---

## Adım 3: Fix Implementasyonu (Implementer)

**Skill:** Implementer

### Minimal Fix Prensibi

- **Sadece bug'ı düzelt:** Refactoring yapma
- **Küçük patch:** Maksimum 3-5 dosya
- **Focused change:** Sadece gerekli değişiklikler

### Patch Formatı

```markdown
## Bug Fix Patch

### Bug ID
[Ticket/Issue numarası]

### Değiştirilen Dosyalar
| Dosya | Değişiklik |
|-------|------------|
| `path/to/file.php` | [Açıklama] |

### Diff
[Unified diff]

### Risk Özeti
- **Seviye:** Düşük
- **Regression Riski:** [Değerlendirme]

### Test Durumu
- Koşuldu mu: Hayır (Tester'a bırakıldı)
```

---

## Adım 4: Test (Tester)

**Skill:** Tester

### 4.1 Bug Fix Testi

**Görev:** Fix'in bug'ı çözüp çözmediğini test et

```markdown
### Bug Fix Test Sonucu

**Bug reproduce adımları tekrarlandı:**
- [ ] Adım 1
- [ ] Adım 2
- [ ] Adım 3

**Sonuç:** 
- [ ] Bug düzeltildi ✅
- [ ] Bug hala var ❌

**Kanıt:**
[Screenshot/log/output]
```

### 4.2 Regression Testi

**Görev:** Fix'in başka bir şey bozup bozmadığını test et

```markdown
### Regression Test Sonucu

**İlgili Testler:**
- [ ] `TestClass::testMethod1` - ✅
- [ ] `TestClass::testMethod2` - ✅

**Manuel Kontroller:**
- [ ] [İlgili özellik 1 çalışıyor]
- [ ] [İlgili özellik 2 çalışıyor]

**Regression Tespit Edildi mi:** Hayır / Evet (detay)
```

### 🚦 Approval Gate 2
> **Her patch sonrası test zorunlu.**

---

## Adım 5: Review (Reviewer)

**Skill:** Reviewer

### Review Checklist

#### Fix Kalitesi
- [ ] Fix root cause'u çözüyor mu?
- [ ] Minimal değişiklik yapılmış mı?
- [ ] Side effect riski var mı?

#### Kod Kalitesi
- [ ] PSR-12 uyumu
- [ ] Error handling
- [ ] Edge case'ler

#### Security (Gerekirse)
- [ ] Security vulnerability fixed?
- [ ] No new vulnerabilities introduced?

### Karar

```markdown
### Review Kararı

**Durum:** GO / NO-GO

**Gerekçe:**
[Karar gerekçesi]

**Gerekli Düzeltmeler (NO-GO ise):**
1. [Düzeltme 1]
2. [Düzeltme 2]
```

### 🚦 Approval Gate 3
> **Reviewer GO/NO-GO kararı verir.**

---

## Bug Severity Levels

| Seviye | Açıklama | Aksiyon |
|--------|----------|---------|
| **Critical** | Production down, data loss | Immediate fix, skip gates |
| **High** | Major feature broken | Same-day fix |
| **Medium** | Feature degraded | Next sprint |
| **Low** | Minor issue, workaround exists | Backlog |

### Critical Bug Prosedürü

Critical bug'lar için hızlandırılmış süreç:

1. **Hotfix branch** oluştur
2. **Minimal fix** yap
3. **Quick test** (sadece affected area)
4. **Deploy** (review sonra)
5. **Post-mortem** review

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [patch-policy.md](../policies/patch-policy.md)
- [security-policy.md](../policies/security-policy.md)
- [laravel-bagisto-change-checklist.md](../checklists/laravel-bagisto-change-checklist.md)
