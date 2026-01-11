# Tools Klasörü

Bu klasör, Bagisto geliştirme sürecinde kullanılan araçları ve referansları içerir.

---

## openagents-upstream (Git Submodule)

**Kaynak:** https://github.com/darrenhinde/OpenAgents

Bu klasör, darrenhinde/OpenAgents projesinin git submodule olarak eklenmiş bir kopyasıdır.

### Amaç

Bu submodule **çalışma zamanı (runtime) bağımlılığı DEĞİLDİR**. Yalnızca referans ve dokümantasyon amaçlı tutulmaktadır:

- OpenAgents workflow pattern'lerini referans almak için
- Skill/policy/playbook yapılarını incelemek için
- Upstream değişikliklerini takip etmek için

### Takip Edilen Branch/Tag

- **Branch:** `main`
- **Commit:** Submodule ekleme tarihindeki son commit

### Submodule Güncelleme Talimatları

#### Submodule'u Güncellemek

```bash
# Repo root'ta çalıştırın
cd tools/openagents-upstream
git fetch origin
git checkout main
git pull origin main
cd ../..
git add tools/openagents-upstream
git commit -m "chore: OpenAgents submodule güncellendi"
```

#### Belirli Bir Tag'e Geçmek

```bash
cd tools/openagents-upstream
git fetch --tags
git checkout <tag-adi>
cd ../..
git add tools/openagents-upstream
git commit -m "chore: OpenAgents submodule <tag-adi> tag'ine güncellendi"
```

#### Yeni Clone'da Submodule'u Almak

```bash
git clone --recurse-submodules <repo-url>
# veya mevcut clone'da:
git submodule update --init --recursive
```

### Lisans ve Atıf

OpenAgents projesi **MIT Lisansı** altında yayınlanmaktadır.

- **Kaynak:** https://github.com/darrenhinde/OpenAgents
- **Lisans:** MIT
- **Telif:** darrenhinde ve katkıda bulunanlar

Bu submodule'u kullanırken orijinal projenin lisansına ve atıf gereksinimlerine uyulmalıdır.

---

## İlgili Dosyalar

- [AGENTS.md](../AGENTS.md) - Ana workflow dokümantasyonu
- [.openagents/QUICKSTART.md](../.openagents/QUICKSTART.md) - Hızlı başlangıç kılavuzu
- [.openagents/VERSIONING.md](../.openagents/VERSIONING.md) - Versiyon takibi
