# E-Ticaret Platformu - Agent Workflow Destekli

Bu repo, Laravel tabanlı e-ticaret platformudur. OpenAgents tabanlı repo-native agent workflow sistemi ile geliştirilmektedir.

---

## 🚀 Quick Start (Dev)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh
php artisan db:seed --class=DevEcommerceSeeder
```

| | URL |
|---|-----|
| **Admin Panel** | http://localhost/admin |
| **Companies Module** | http://localhost/admin/mockupsoft/companies |
| **Login** | `admin@example.com` / `admin123` |

### Tenant DDL Testleri (SaaS)

### Domain Verification (Patch-12)

Custom domain doğrulama iki yöntemle yapılır:
- **DNS TXT:** `_saas-verify.<domain>` hostuna `saas-verify=<token>` TXT kaydı ekleyin.
- **HTTP File:** `https://<domain>/.well-known/saas-domain-verification.txt` endpoint’i `saas-verify=<token>` içeriğini döndürmeli.

Lokal/test için DNS/HTTP doğrulama I/O mocklanır:
- HTTP: `Http::fake()`
- DNS: testte `app()->instance(DomainVerificationService::class, new DomainVerificationService($fakeResolver))`


Bazı testler (ör. `TenantCustomerHttpSmokeTest`, `TenantCustomerIsolationTest`, `TenantSalesCheckoutSmokeTest`) tenant DB oluşturup migration çalıştırdığı için varsayılan olarak **skip** edilir.

- Açmak için: `.env.testing` (veya test ortamı env) içine `RUN_TENANT_DDL_TESTS=true` ekleyin.
- MySQL kullanıcısının `CREATE DATABASE` / `DROP DATABASE` yetkisi olmalı.
- Örnek komutlar:
  - `RUN_TENANT_DDL_TESTS=true php artisan migrate --path=database/migrations/tenant -v`
  - `RUN_TENANT_DDL_TESTS=true php artisan test --filter=TenantCustomer`
  - `RUN_TENANT_DDL_TESTS=true php artisan test --filter=ProvisioningFlowTest`
  - `RUN_TENANT_DDL_TESTS=true php artisan test --filter=TenantSalesCheckoutSmokeTest`

### Patch-13 Test Komutları

- `php artisan test --filter=MerchantTenantManagementTest`
- `php artisan test --filter=AdminTenantManagementTest`

> 📖 Ayrıntılar için [`docs/dev.md`](docs/dev.md)

---

## İçindekiler

- [Genel Bakış](#genel-bakış)
- [Hızlı Başlangıç](#hızlı-başlangıç)
- [Agent Workflow Sistemi](#agent-workflow-sistemi)
- [Proje Yapısı](#proje-yapısı)
- [Geliştirme Kuralları](#geliştirme-kuralları)
- [Katkıda Bulunma](#katkıda-bulunma)
- [Lisans](#lisans)

---

## Genel Bakış

### Bu Repo Nedir?

- **Repository:** `mockupsoft/ecommerce` - Geliştirme burada yapılır
- **Workflow:** OpenAgents tabanlı, local-first, küçük patch'lerle geliştirme

### Temel Özellikler

- **Agent Workflow:** Claude Code 2.1 "/skills" davranışını taklit eden yapılandırılmış geliştirme süreci
- **Local-First:** Ollama + qwen3-coder:30b ile yerel model kullanımı
- **Küçük Patch'ler:** Her değişiklik max 5-10 dosya, test zorunlu
- **Onay Kapıları:** Kritik alanlarda (checkout, payment, ACL, auth) Orchestrator onayı gerekli

---

## Hızlı Başlangıç

### 1. Repo'yu Klonlayın

```bash
git clone --recurse-submodules https://github.com/mockupsoft/ecommerce.git
cd ecommerce
```

> **Not:** `--recurse-submodules` flag'i `tools/openagents-upstream` submodule'ünü de indirir.

### 2. Ollama Kurulumu

Ollama'yı kurun ve modeli indirin:

```bash
ollama pull qwen3-coder:30b
```

Ollama varsayılan olarak `localhost:11434` üzerinden çalışır. Farklıysa OpenCode provider ayarından doğrulayın.

### 3. Agent Workflow'u Kullanın

Detaylı kullanım için: [.openagents/QUICKSTART.md](.openagents/QUICKSTART.md)

---

## Agent Workflow Sistemi

### Workflow Akışı

```
┌─────────┐    ┌───────┐    ┌─────────┐    ┌────────┐    ┌─────────┐
│  PLAN   │ → │ ONAY  │ → │  PATCH  │ → │  TEST  │ → │ REVIEW  │
└─────────┘    └───────┘    └─────────┘    └────────┘    └─────────┘
     │              │             │             │             │
     ▼              ▼             ▼             ▼             ▼
 Architect      Orchestrator  Implementer    Tester      Reviewer
```

### Skill'ler (Sub-Agent'lar)

| Skill | Amaç | Dosya |
|-------|------|-------|
| **Orchestrator** | PM/Lead rolü, routing, onay kapıları | [orchestrator.md](.openagents/skills/orchestrator.md) |
| **Repo-Scout** | Dosya/pattern bulma, keşif | [repo-scout.md](.openagents/skills/repo-scout.md) |
| **Architect** | Tasarım planı, risk analizi | [architect.md](.openagents/skills/architect.md) |
| **Implementer** | Minimal diff/patch üretimi | [implementer.md](.openagents/skills/implementer.md) |
| **Tester** | Test çalıştırma, sonuç yorumlama | [tester.md](.openagents/skills/tester.md) |
| **Reviewer** | Diff review, go/no-go kararı | [reviewer.md](.openagents/skills/reviewer.md) |
| **Doc-Writer** | Dokümantasyon, PR açıklaması | [doc-writer.md](.openagents/skills/doc-writer.md) |

### Skill Separation Kuralları

- **Implementer** plan yapmaz (sadece verilen planı uygular)
- **Architect** kod yazmaz (sadece plan üretir)
- **Reviewer** kod düzeltmez (sadece inceleme yapar)
- **Orchestrator** dosya düzenlemez (sadece yönlendirir)
- **Repo-Scout** sadece keşif yapar (değişiklik yok, yorum/refactor önermez)
- **Tester** kod yazmaz (sadece fix önerisi verir, fix'i Implementer yapar)

---

## Proje Yapısı

```
mockupsoft/ecommerce/
├── AGENTS.md                    # Ana workflow dokümantasyonu
├── README.md                    # Bu dosya
├── .gitmodules                  # Submodule konfigürasyonu
│
├── .openagents/                 # Agent workflow dosyaları
│   ├── QUICKSTART.md            # 5 dakikada başlangıç
│   ├── VERSIONING.md            # Versiyon takibi
│   │
│   ├── skills/                  # Skill tanımları (7 dosya)
│   │   ├── orchestrator.md
│   │   ├── repo-scout.md
│   │   ├── architect.md
│   │   ├── implementer.md
│   │   ├── tester.md
│   │   ├── reviewer.md
│   │   └── doc-writer.md
│   │
│   ├── policies/                # Politikalar (3 dosya)
│   │   ├── patch-policy.md      # Patch kuralları
│   │   ├── security-policy.md   # Güvenlik kuralları
│   │   └── php-laravel-style.md # Kod stili
│   │
│   ├── playbooks/               # Adım adım süreçler (4 dosya)
│   │   ├── bagisto-admin-crud.md
│   │   ├── bagisto-module-skeleton.md
│   │   ├── bagisto-migration-and-seed.md
│   │   └── bagisto-bugfix-protocol.md
│   │
│   ├── checklists/              # Kontrol listeleri
│   │   └── laravel-bagisto-change-checklist.md
│   │
│   └── templates/               # Çıktı şablonları (4 dosya)
│       ├── plan-template.md
│       ├── patch-summary-template.md
│       ├── review-template.md
│       └── pr-description-template.md
│
└── tools/
    ├── README.md                # Tools klasörü açıklaması
    └── openagents-upstream/     # Git submodule (darrenhinde/OpenAgents)
```

---

## Geliştirme Kuralları

### Kırmızı Kurallar (İhlal Edilemez)

> ⚠️ Aşağıdaki alanlarda değişiklik yapılmadan önce **Orchestrator'dan açık onay** alınmalıdır:

| Alan | Risk Seviyesi |
|------|---------------|
| Checkout Core | 🔴 Yüksek |
| Payment Core | 🔴 Yüksek |
| ACL/Auth | 🔴 Yüksek |
| DB Migration | 🔴 Yüksek |
| Role Escalation | 🔴 Yüksek |

### Patch Kuralları

- Her patch maksimum **5-10 dosya** içermelidir
- Her patch sonrası **test zorunludur**
- Her patch için **risk özeti** yazılmalıdır
- Büyük değişiklikler küçük patch'lere bölünür

### Kod Stili

- **PSR-12** standartlarına uyum
- **Laravel conventions** takip edilmeli
- **E-Commerce patterns** kullanılmalı (Repository, DataGrid, etc.)

Detaylar için: [php-laravel-style.md](.openagents/policies/php-laravel-style.md)

---

## Katkıda Bulunma

### Workflow

1. **Orchestrator'a plan sor:** "Bu değişiklik için plan oluştur"
2. **Planı incele ve onayla**
3. **Implementer'dan patch iste:** "Planın 1. adımını uygula"
4. **Tester'dan test iste:** "Patch'i test et"
5. **Reviewer'dan review iste:** "Değişiklikleri incele"

### Örnek Prompt'lar

```
Orchestrator olarak davran. Admin paneline yeni bir "Raporlar" modülü eklemek istiyorum.
Bunun için hangi skill'lerin hangi sırayla çalışacağını belirle.
```

```
Architect olarak davran. Admin paneline yeni bir CRUD modülü eklemek için
dosya bazlı implementasyon planı oluştur.
```

```
Implementer olarak davran. Architect'in planındaki 1. adımı uygula.
Maximum 5 dosya değiştir ve risk özeti ekle.
```

---

## Referanslar

### Repo İçi Linkler

- [AGENTS.md](AGENTS.md) - Ana workflow dokümantasyonu
- [.openagents/QUICKSTART.md](.openagents/QUICKSTART.md) - Hızlı başlangıç
- [.openagents/VERSIONING.md](.openagents/VERSIONING.md) - Versiyon takibi

### Upstream Referanslar

- **OpenAgents:** [darrenhinde/OpenAgents](https://github.com/darrenhinde/OpenAgents) (workflow referansı)

> **Not:** `tools/openagents-upstream` çalışma zamanı bağımlılığı **değildir**. Yalnızca referans amaçlı upstream kopyadır.

---

## Model Kullanımı

### Varsayılan: Ollama (Local-First)

| Özellik | Değer |
|---------|-------|
| Endpoint | `localhost:11434` (varsayılan) |
| Model | `qwen3-coder:30b` (önerilen) |
| Fallback | Claude (sadece açıkça talep edildiğinde) |

### Yavaş Sistem İçin

- Daha küçük coder modelleri kullanılabilir
- Vendor lock-in yok, cloud push yok

---

## Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.


---

## Neden Bu Yaklaşım?

### Büyük Repo'larda Guardrail'ler

- **Küçük patch'ler:** Review kolaylaşır, hata riski azalır
- **Onay kapıları:** Kritik alanlarda kontrollü değişiklik
- **Skill separation:** Her rol kendi sınırları içinde kalır
- **Test zorunluluğu:** Her değişiklik sonrası doğrulama

### Local-First Avantajları

- Vendor lock-in yok
- Data privacy (kod cloud'a gitmez)
- Düşük maliyet
- Offline çalışabilme

---

*Bu workflow sistemi Claude Code 2.1 "/skills" davranışını taklit eder ve [OpenAgents](https://github.com/darrenhinde/OpenAgents) projesinden ilham almıştır.*
