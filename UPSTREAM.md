# Upstream Senkronizasyon Rehberi

Bu doküman, `mockupsoft/bagisto` fork'unun `bagisto/bagisto` upstream'i ile nasıl senkronize edileceğini açıklar.

---

## Upstream Nedir?

| Terim | Açıklama |
|-------|----------|
| **Origin** | `mockupsoft/bagisto` - Bu fork, geliştirme burada yapılır |
| **Upstream** | `bagisto/bagisto` - Orijinal Bagisto projesi, referans kaynağı |

Fork stratejisi:
- Tüm özelleştirmeler ve workflow dosyaları bu fork'ta tutulur
- Upstream'den düzenli olarak güncellemeler alınır
- Uyumluluk korunarak ilerlenir

---

## Remote Yapılandırması

```bash
# Mevcut remote'ları kontrol et
git remote -v

# Beklenen çıktı:
# origin    https://github.com/mockupsoft/bagisto.git (fetch)
# origin    https://github.com/mockupsoft/bagisto.git (push)
# upstream  https://github.com/bagisto/bagisto.git (fetch)
# upstream  https://github.com/bagisto/bagisto.git (push)

# Eğer upstream yoksa ekle:
git remote add upstream https://github.com/bagisto/bagisto.git
```

---

## Senkronizasyon Stratejisi

### 1. Upstream Değişikliklerini Çekme

```bash
# Upstream'den fetch yap
git fetch upstream --tags

# Default branch'i tespit et
git remote show upstream | findstr "HEAD branch"

# Veya doğrudan branch'i çek
git fetch upstream 2.3
```

### 2. Merge Stratejisi

**Önerilen yaklaşım:** Rebase yerine merge kullan (conflict yönetimi daha kolay)

```bash
# Önce preserve dosyalarını yedekle
mkdir -p .tmp_preserve
cp -r AGENTS.md .openagents tools .gitmodules .tmp_preserve/

# Upstream'i merge et
git merge upstream/2.3 --no-edit

# Conflict varsa çöz, sonra preserve dosyalarını geri yükle
cp -r .tmp_preserve/AGENTS.md .
cp -r .tmp_preserve/.openagents .
cp -r .tmp_preserve/tools .
cp -r .tmp_preserve/.gitmodules .

# Temizle
rm -rf .tmp_preserve

# Commit
git add .
git commit -m "chore: sync with upstream bagisto/bagisto"
```

### 3. Tag/Release Takibi

```bash
# Stable release tag'leri listele (rc/beta/alpha hariç)
git tag -l "v*" --sort=-v:refname | findstr /V /I "rc beta alpha" | Select-Object -First 10

# Belirli bir tag'e güncelle
git checkout v2.3.10 -- <specific-files>
```

---

## Korunan Dosyalar (Asla Üzerine Yazılmayacak)

Upstream senkronizasyonu sırasında şu dosyalar **mutlaka korunmalıdır**:

| Dosya/Klasör | Açıklama | Conflict Kuralı |
|--------------|----------|-----------------|
| `AGENTS.md` | Agent workflow dokümantasyonu | Bizimkini koru |
| `.openagents/` | Skill, policy, playbook dosyaları | Bizimkini koru |
| `tools/openagents-upstream/` | OpenAgents submodule | Bizimkini koru |
| `tools/README.md` | Tools klasörü açıklaması | Bizimkini koru |
| `.gitmodules` | Submodule konfigürasyonu | Bizimkini koru |
| `UPSTREAM.md` | Bu dosya | Bizimkini koru |

### Özel Durumlar

| Dosya | Kural |
|-------|-------|
| `README.md` | Upstream'i al, workflow bölümünü ekle |
| `.gitignore` | İki tarafı birleştir (merge) |
| `.gitattributes` | Upstream'i tercih et |

---

## Conflict Çözüm Kuralları

### Öncelik Sırası

1. **Workflow dosyaları** → Her zaman bizimkini koru
2. **Bagisto core dosyaları** → Upstream'i tercih et
3. **Konfigürasyon dosyaları** → Dikkatli merge et

### Conflict Çözüm Adımları

```bash
# 1. Conflict'li dosyaları listele
git status | findstr "both modified"

# 2. Her dosya için karar ver
# Bizimkini koru:
git checkout --ours <file>

# Upstream'i al:
git checkout --theirs <file>

# Manuel merge:
# Dosyayı aç, conflict marker'larını düzenle

# 3. Çözülen dosyaları stage'e ekle
git add <file>

# 4. Merge'i tamamla
git commit
```

---

## Güncelleme Kontrol Listesi

Upstream senkronizasyonu yapmadan önce:

- [ ] Mevcut branch temiz mi? (`git status`)
- [ ] Workflow dosyaları yedeklendi mi?
- [ ] Hangi upstream sürümüne güncelleniyor? (tag/branch)
- [ ] Breaking change var mı? (CHANGELOG kontrol)

Senkronizasyon sonrası:

- [ ] Workflow dosyaları yerinde mi?
- [ ] Submodule sağlam mı? (`git submodule status`)
- [ ] Testler geçiyor mu?
- [ ] README.md workflow bölümü mevcut mu?

---

## Mevcut Upstream Bilgisi

| Özellik | Değer |
|---------|-------|
| **Upstream URL** | https://github.com/bagisto/bagisto |
| **Import Edilen Versiyon** | v2.3.10 |
| **Import Tarihi** | 2026-01-11 |
| **Upstream Default Branch** | 2.3 |

---

## Referanslar

- [AGENTS.md](AGENTS.md) - Agent workflow dokümantasyonu
- [.openagents/QUICKSTART.md](.openagents/QUICKSTART.md) - Hızlı başlangıç
- [.openagents/VERSIONING.md](.openagents/VERSIONING.md) - Versiyon takibi
- [Bagisto Upstream](https://github.com/bagisto/bagisto) - Orijinal proje

---

*Bu dosya upstream senkronizasyonu için referans noktasıdır. Güncel tutulmalıdır.*
