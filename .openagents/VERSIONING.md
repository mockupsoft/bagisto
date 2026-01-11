# Versiyon Takibi

Bu doküman, `.openagents/` workflow sisteminin ve upstream referansların versiyon takibini sağlar.

---

## Mevcut Versiyonlar

### .openagents/ Doküman Versiyonu

| Özellik | Değer |
|---------|-------|
| **Versiyon** | 1.0.0 |
| **Tarih** | 2026-01-11 |
| **Son Güncelleme** | İlk oluşturma |

### OpenAgents Upstream Referansı

| Özellik | Değer |
|---------|-------|
| **Kaynak** | https://github.com/darrenhinde/OpenAgents |
| **Konum** | `tools/openagents-upstream/` |
| **Branch** | `main` |
| **Commit** | Submodule ekleme tarihindeki son commit |

---

## Değişiklik Yapınca Ne Güncellenecek?

### Submodule Güncellendiğinde

Aşağıdaki dosyalar kontrol edilmeli:

1. **Bu dosya (`VERSIONING.md`):**
   - "OpenAgents Upstream Referansı" bölümünü güncelle
   - Commit hash'ini yeni commit ile değiştir

2. **Skill dosyaları (`skills/*.md`):**
   - Upstream'deki skill tanımlarıyla uyumluluğu kontrol et
   - Gerekirse güncellemeleri yansıt

3. **README_MIRROR.md (`tools/openagents-upstream/README_MIRROR.md`):**
   - Takip edilen branch/tag bilgisini güncelle

### Doküman Değişikliği Yapıldığında

1. **Bu dosyadaki "Doküman Versiyonu" bölümünü güncelle:**
   - Versiyon numarasını artır (semver)
   - Tarihi güncelle
   - Değişiklik açıklaması ekle

2. **Versiyon numaralama kuralları:**
   - **Major (X.0.0):** Breaking changes, skill tanımları değişti
   - **Minor (0.X.0):** Yeni skill/policy/playbook eklendi
   - **Patch (0.0.X):** Küçük düzeltmeler, typo'lar

---

## Drift Önleme Stratejisi

### Haftalık Kontrol

1. Upstream OpenAgents repo'sunu kontrol et
2. Yeni commit'ler varsa değişiklikleri incele
3. Uyumlu değişiklikleri local dosyalara yansıt

### Submodule Güncelleme Prosedürü

```bash
# 1. Submodule'u güncelle
cd tools/openagents-upstream
git fetch origin
git checkout main
git pull origin main

# 2. Değişiklikleri kontrol et
git log --oneline -10

# 3. Ana repo'ya commit et
cd ../..
git add tools/openagents-upstream
git add .openagents/VERSIONING.md  # Güncellendiyse
git commit -m "chore: OpenAgents submodule güncellendi"
```

### Uyumsuzluk Durumu

Upstream'de breaking change varsa:

1. Local skill/policy dosyalarını incele
2. Gerekli güncellemeleri yap
3. **Major versiyon artır**
4. Değişiklik logunu güncelle

---

## Değişiklik Geçmişi

### v1.0.0 (2026-01-11)

- İlk oluşturma
- 7 skill tanımı eklendi
- 3 policy eklendi
- 4 playbook eklendi
- 4 template eklendi
- 1 checklist eklendi

---

## İlgili Dosyalar

- [AGENTS.md](../AGENTS.md)
- [QUICKSTART.md](QUICKSTART.md)
- [tools/openagents-upstream/README_MIRROR.md](../tools/openagents-upstream/README_MIRROR.md)
