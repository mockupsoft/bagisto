# Hızlı Başlangıç Kılavuzu

5 dakikada agent workflow sistemini kullanmaya başlayın.

## İçindekiler

1. [Ön Gereksinimler](#ön-gereksinimler)
2. [Kurulum](#kurulum)
3. [Model Yapılandırması](#model-yapılandırması)
4. [İlk Görev](#ilk-görev)
5. [Örnek Prompt'lar](#örnek-promptlar)
6. [Sorun Giderme](#sorun-giderme)

---

## Ön Gereksinimler

- OpenCode (CLI veya Desktop versiyonu)
- Ollama (local model runner)
- Bu repo'nun klonlanmış kopyası

---

## Kurulum

### 1. Repo'yu Klonlayın

```bash
git clone --recurse-submodules https://github.com/mockupsoft/bagisto.git
cd bagisto
```

> **Not:** `--recurse-submodules` flag'i `tools/openagents-upstream` submodule'unu da indirir.

### 2. Ollama'yı Başlatın

Ollama'nın çalıştığından emin olun. Varsayılan olarak `localhost:11434` üzerinden çalışır.

> **Farklı bir endpoint kullanıyorsanız:** OpenCode provider ayarından endpoint'i doğrulayın.

### 3. Model İndirin

```bash
ollama pull qwen3-coder:30b
```

---

## Model Yapılandırması

### Varsayılan Model

- **Model:** `qwen3-coder:30b`
- **Endpoint:** `localhost:11434` (varsayılan)

### Yavaş Sistem İçin Alternatifler

Eğer `qwen3-coder:30b` sisteminizde yavaş çalışıyorsa, daha küçük coder modelleri kullanabilirsiniz:

- Daha küçük parametre sayılı coder modelleri
- Quantized versiyonlar

> **Not:** Vendor lock-in yok. İstediğiniz herhangi bir local model kullanabilirsiniz. Cloud servislere zorunlu bağımlılık yoktur.

### OpenCode'da Model Ayarlama

OpenCode ayarlarından:
1. Provider olarak Ollama'yı seçin
2. Endpoint'i doğrulayın (`localhost:11434` veya özel endpoint)
3. Model adını girin (`qwen3-coder:30b` veya alternatif)

---

## İlk Görev

### Workflow'u Anlamak

Her görev şu akışı takip eder:

```
Plan → Onay → Patch → Test → Review
```

### Küçük Patch Prensibi

- Her patch maksimum **5-10 dosya** içermelidir
- Her patch sonrası **test zorunludur**
- Her patch için **risk özeti** yazılmalıdır
- Büyük değişiklikler küçük patch'lere bölünür

### Onay Kapıları

Kritik alanlarda (checkout, payment, ACL, auth, migrations) Orchestrator'dan onay alınmadan ilerlenmez.

---

## Örnek Prompt'lar

### 1. Yeni Özellik İçin Plan İste

```
Orchestrator olarak davran. Admin paneline yeni bir "Raporlar" modülü eklemek istiyorum.
Bunun için:
1. Hangi skill'lerin hangi sırayla çalışacağını belirle
2. Onay gerektiren noktaları listele
3. Risk analizini yap
```

### 2. Mevcut Kodu Keşfet

```
Repo-scout olarak davran. Bagisto'da ürün listeleme (product listing) ile ilgili 
controller ve view dosyalarını bul. Sadece dosya yollarını listele, değişiklik önerme.
```

### 3. Tasarım Planı İste

```
Architect olarak davran. Admin paneline yeni bir CRUD modülü eklemek için:
1. Dosya bazlı implementasyon planı oluştur
2. Her dosya için ne yapılacağını belirt
3. Migration riskleri varsa listele
4. Test stratejisini tanımla
```

### 4. Patch Üret

```
Implementer olarak davran. Architect'in planındaki 1. adımı uygula:
- Controller dosyasını oluştur
- Route tanımlarını ekle
- Maximum 5 dosya değiştir
- Risk özeti ekle
```

### 5. Test Çalıştır

```
Tester olarak davran. Son patch için:
1. İlgili testleri çalıştır
2. Sonuçları raporla
3. Hata varsa minimum fix önerisi ver (kodu sen yazma)
```

### 6. Review Yap

```
Reviewer olarak davran. Son patch için:
1. Diff'i incele
2. Güvenlik kontrolü yap
3. Performans etkisini değerlendir
4. Go/No-Go kararını ver
```

---

## Sorun Giderme

### Ollama Bağlantı Sorunu

1. Ollama'nın çalıştığını kontrol edin
2. Endpoint'in doğru olduğunu doğrulayın
3. Firewall ayarlarını kontrol edin

### Model Yavaş Çalışıyor

1. Daha küçük bir model deneyin
2. Quantized versiyon kullanın
3. Sistem kaynaklarını kontrol edin

### Submodule Eksik

```bash
git submodule update --init --recursive
```

---

## İlgili Dosyalar

- **Ana Dokümantasyon:** [AGENTS.md](../AGENTS.md)
- **Versiyon Takibi:** [VERSIONING.md](VERSIONING.md)
- **Skills:** [skills/](skills/)
- **Policies:** [policies/](policies/)
- **Playbooks:** [playbooks/](playbooks/)
- **Upstream Referans:** [tools/openagents-upstream/](../tools/openagents-upstream/)

---

*5 dakikada başladınız! Şimdi örnek prompt'lardan birini deneyin.*
