# Agent Workflow Dokümantasyonu

Bu doküman, Bagisto projesi için repo-native agent workflow sistemini tanımlar. Sistem, Claude Code 2.1'in "Sub Agents /skills" davranışını taklit eder ve OpenCode (CLI/Desktop) ile çalışacak şekilde tasarlanmıştır.

## İçindekiler

1. [Genel Bakış](#genel-bakış)
2. [Workflow: Plan → Onay → Patch → Test → Review](#workflow-plan--onay--patch--test--review)
3. [Skill/Sub-Agent Kavramı](#skillsub-agent-kavramı)
4. [OpenCode Entegrasyonu](#opencode-entegrasyonu)
5. [Model Kullanımı](#model-kullanımı)
6. [Neden Upstream Bagisto'ya Sadık Kalıyoruz?](#neden-upstream-bagistoya-sadık-kalıyoruz)
7. [Kabul Edilen Çıktı Formatları](#kabul-edilen-çıktı-formatları)
8. [Referanslar](#referanslar)

---

## Genel Bakış

Bu repo şu şekilde çalışır:

- **Fork:** `mockupsoft/bagisto` - Geliştirme burada yapılır
- **Upstream:** `bagisto/bagisto` - Referans ve uyumluluk kaynağı
- **Workflow Referansı:** `darrenhinde/OpenAgents` → `tools/openagents-upstream` (git submodule)

> **KRİTİK NOT:** `tools/openagents-upstream` çalışma zamanı (runtime) bağımlılığı **DEĞİLDİR**. Yalnızca referans amaçlı upstream kopyadır. Composer veya npm bağımlılığı olarak eklenmez.

---

## Workflow: Plan → Onay → Patch → Test → Review

Her değişiklik şu adımları takip eder:

```
┌─────────┐    ┌───────┐    ┌─────────┐    ┌────────┐    ┌─────────┐
│  PLAN   │ → │ ONAY  │ → │  PATCH  │ → │  TEST  │ → │ REVIEW  │
└─────────┘    └───────┘    └─────────┘    └────────┘    └─────────┘
     │              │             │             │             │
     ▼              ▼             ▼             ▼             ▼
 Architect      Orchestrator  Implementer    Tester      Reviewer
   planı          onayı       patch üretir  test koşar   go/no-go
```

### Adım Detayları

1. **PLAN:** Architect skill'i detaylı implementasyon planı oluşturur
2. **ONAY:** Orchestrator planı inceler, onay kapılarını belirler
3. **PATCH:** Implementer küçük, odaklı patch'ler üretir (max 5-10 dosya)
4. **TEST:** Tester test suite'ini çalıştırır, sonuçları raporlar
5. **REVIEW:** Reviewer güvenlik/performans/uyumluluk kontrolü yapar, go/no-go kararı verir

---

## Skill/Sub-Agent Kavramı

Her skill belirli bir rol üstlenir ve **sadece o rolün sınırları içinde** hareket eder.

### Skill Listesi

| Skill | Amaç | Dosya |
|-------|------|-------|
| **Orchestrator** | PM/Lead rolü, routing, onay kapıları | [orchestrator.md](.openagents/skills/orchestrator.md) |
| **Repo-Scout** | Dosya/pattern bulma, keşif | [repo-scout.md](.openagents/skills/repo-scout.md) |
| **Architect** | Tasarım planı, risk analizi | [architect.md](.openagents/skills/architect.md) |
| **Implementer** | Minimal diff/patch üretimi | [implementer.md](.openagents/skills/implementer.md) |
| **Tester** | Test çalıştırma, sonuç yorumlama | [tester.md](.openagents/skills/tester.md) |
| **Reviewer** | Diff review, go/no-go kararı | [reviewer.md](.openagents/skills/reviewer.md) |
| **Doc-Writer** | Dokümantasyon, PR açıklaması | [doc-writer.md](.openagents/skills/doc-writer.md) |

### Skill Separation Kuralları (Kesin Sınırlar)

- **Implementer** plan yapmaz (sadece verilen planı uygular)
- **Architect** kod yazmaz (sadece plan üretir)
- **Reviewer** kod düzeltmez (sadece inceleme yapar)
- **Orchestrator** dosya düzenlemez (sadece yönlendirir)
- **Repo-Scout** sadece keşif yapar (değişiklik yok, yorum/refactor önermez)
- **Tester** kod yazmaz (sadece "hata var/yok" ve minimum fix önerisi verir, fix'i Implementer yapar)

> Bu yaklaşım Claude Code 2.1 `/skills` davranışını taklit eder.

---

## OpenCode Entegrasyonu

Agent, bu repo'daki `.openagents/` klasöründeki dosyaları okuyup talimatlara uymalıdır.

### Agent'ın Okuması Gereken Dosyalar

```
.openagents/
├── skills/           # Skill tanımları
├── policies/         # Politikalar (güvenlik, stil, patch)
├── playbooks/        # Adım adım süreçler
├── checklists/       # Kontrol listeleri
├── templates/        # Çıktı şablonları
├── QUICKSTART.md     # Hızlı başlangıç
└── VERSIONING.md     # Versiyon takibi
```

### Önerilen Çalışma Akışı

1. **Orchestrator'a plan sor:** "Bu değişiklik için plan oluştur"
2. **Planı incele ve onayla**
3. **Implementer'dan patch iste:** "Planın 1. adımını uygula"
4. **Tester'dan test iste:** "Patch'i test et"
5. **Reviewer'dan review iste:** "Değişiklikleri incele"

---

## Model Kullanımı

### Varsayılan: Ollama (Local-First)

- **Endpoint:** `localhost:11434` (varsayılan)
- **Model:** `qwen3-coder:30b` (önerilen)
- **Not:** Endpoint farklıysa OpenCode provider ayarından doğrulayın

### Alternatif Modeller (Yavaş Sistem İçin)

- Daha küçük coder modelleri kullanılabilir
- Vendor lock-in yok, cloud push yok
- Detaylar için: [QUICKSTART.md](.openagents/QUICKSTART.md)

### Claude Fallback (Opsiyonel)

Claude yalnızca **açıkça talep edildiğinde** kullanılır. Varsayılan olarak kullanılmaz.

---

## Neden Upstream Bagisto'ya Sadık Kalıyoruz?

### 1. Fork Stratejisi

- `mockupsoft/bagisto` fork'unda geliştirme yapılır
- `bagisto/bagisto` ile uyumluluk korunur
- Upstream değişiklikler düzenli olarak merge edilir

### 2. Core Değişiklik Politikası

Core alanlar (checkout, payment, auth, ACL, migrations) için:

- **Her değişiklik review gerektirir**
- **Orchestrator'dan açık onay alınmadan Implementer patch üretmez**
- **Rol/yetki değişiklikleri (role escalation riski) özel onay ister**

### 3. Küçük Patch Prensibi

- Her patch maksimum 5-10 dosya içerir
- Her patch sonrası test zorunludur
- Her patch için risk özeti yazılır
- Büyük değişiklikler küçük patch'lere bölünür

---

## Kabul Edilen Çıktı Formatları

### Implementer Patch Output Formatı

```markdown
## Patch Özeti

### Değiştirilen Dosyalar
- `path/to/file1.php` - [Açıklama]
- `path/to/file2.php` - [Açıklama]

### Diff
[Unified diff formatında değişiklikler]

### Risk Özeti
- **Düşük/Orta/Yüksek:** [Risk açıklaması]
- **Etkilenen Alanlar:** [Liste]

### Test Planı
- [ ] [Test 1]
- [ ] [Test 2]
- Koşuldu mu: Evet/Hayır
```

### Diğer Skill Çıktı Formatları

| Skill | Çıktı Formatı |
|-------|---------------|
| Orchestrator | Skill routing planı, onay noktaları |
| Repo-Scout | Dosya listesi, pattern analizi |
| Architect | Plan, risk listesi, test stratejisi |
| Tester | Test sonuçları, fix önerileri |
| Reviewer | Go/No-Go kararı, güvenlik/performans notları |
| Doc-Writer | Release notes, PR açıklaması |

Detaylı şablonlar için: [templates/](.openagents/templates/)

---

## Referanslar

### Repo İçi Linkler

- **Hızlı Başlangıç:** [QUICKSTART.md](.openagents/QUICKSTART.md)
- **Versiyon Takibi:** [VERSIONING.md](.openagents/VERSIONING.md)
- **Skills:** [.openagents/skills/](.openagents/skills/)
- **Policies:** [.openagents/policies/](.openagents/policies/)
- **Playbooks:** [.openagents/playbooks/](.openagents/playbooks/)
- **Checklists:** [.openagents/checklists/](.openagents/checklists/)
- **Templates:** [.openagents/templates/](.openagents/templates/)

### Upstream Referanslar

- **OpenAgents (Workflow Referansı):** [tools/openagents-upstream/](tools/openagents-upstream/)
- **Bagisto Upstream:** https://github.com/bagisto/bagisto
- **Bu Fork:** https://github.com/mockupsoft/bagisto

---

## OpenCode GitHub Entegrasyonu

Bu repo OpenCode GitHub Action ile entegre edilmiştir. GitHub Issues ve Pull Request'lerde `/opencode` veya `/oc` komutları ile agent'ı tetikleyebilirsiniz.

### Kurulum

1. **GitHub Secrets:** Repository Settings → Secrets and variables → Actions → `GEMINI_API_KEY` ekleyin
2. **Workflow:** `.github/workflows/opencode.yml` dosyası otomatik olarak yapılandırılmıştır
3. **Config:** `opencode.json` dosyası OpenCode'a bağlayıcı talimatları sağlar

### Kullanım

- **Issue comment:** `/opencode <istek>` → Sadece PLAN üretilir (kod/patch üretilmez)
- **PR review comment:** `/opencode <istek>` → PATCH (unified diff) + TEST PLAN üretilir

**Örnek:**
```
/opencode Repo-Scout olarak tenant domain resolver ile ilgili dosyaları listele
```

### Bağlayıcı Talimatlar

OpenCode şu dosyaları bağlayıcı talimat olarak okur:
- `opencode.json` içindeki `instructions` listesi (AGENTS.md + .openagents/** dosyaları)
- `README.md` (otomatik)

`.opencode.yaml` varsa dokümantasyon amaçlıdır (OpenCode otomatik okumaz).

---

*Bu doküman `.openagents/` workflow sisteminin ana referans noktasıdır.*
