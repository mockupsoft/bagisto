# Playbook: Admin CRUD Ekleme

Bu playbook, Bagisto admin paneline yeni bir CRUD modülü ekleme sürecini tanımlar.

---

## Genel Bakış

**Amaç:** Admin paneline yeni bir varlık (entity) için CRUD işlemleri eklemek

**Tipik Akış:**
```
Routes → Controller → Repository → Model → Datagrid → Views → ACL → Lang
```

---

## Kırmızı Kural

> ⚠️ **ACL/Auth core değişiklikleri için Orchestrator'dan açık onay alınmadan Implementer patch üretmez.**
>
> ⚠️ **Rol/yetki seviyelerini etkileyen değişiklikler (role escalation riski) Orchestrator onayı gerektirir.**

---

## Skill Mapping

| Adım | Skill | Görev |
|------|-------|-------|
| 1 | Repo-Scout | Mevcut CRUD pattern'lerini keşfet |
| 2 | Architect | Implementasyon planı oluştur |
| 3 | Implementer | Kodu yaz (küçük patch'ler halinde) |
| 4 | Tester | Testleri çalıştır |
| 5 | Reviewer | Review yap, go/no-go kararı |
| 6 | Doc-Writer | PR açıklaması yaz |

---

## Adım 1: Keşif (Repo-Scout)

**Skill:** Repo-Scout

**Görev:** Mevcut admin CRUD pattern'lerini bul

**Aranacak Dosyalar:**
- `packages/Webkul/*/Http/Controllers/Admin/`
- `packages/Webkul/*/DataGrids/`
- `packages/Webkul/*/Resources/views/admin/`

**Çıktı:**
- Örnek controller listesi
- Datagrid örnekleri
- View yapısı örnekleri

---

## Adım 2: Planlama (Architect)

**Skill:** Architect

**Görev:** Dosya bazlı implementasyon planı oluştur

**Plan İçeriği:**

### 2.1 Model ve Repository
- Model dosyası: `packages/Webkul/[Module]/src/Models/[Entity].php`
- Repository: `packages/Webkul/[Module]/src/Repositories/[Entity]Repository.php`

### 2.2 Controller
- Controller: `packages/Webkul/[Module]/src/Http/Controllers/Admin/[Entity]Controller.php`
- Request: `packages/Webkul/[Module]/src/Http/Requests/[Entity]Request.php`

### 2.3 Routes
- Route dosyası: `packages/Webkul/[Module]/src/Routes/admin-routes.php`

### 2.4 Datagrid
- Datagrid: `packages/Webkul/[Module]/src/DataGrids/[Entity]DataGrid.php`

### 2.5 Views
- Index: `packages/Webkul/[Module]/src/Resources/views/admin/[entity]/index.blade.php`
- Create: `packages/Webkul/[Module]/src/Resources/views/admin/[entity]/create.blade.php`
- Edit: `packages/Webkul/[Module]/src/Resources/views/admin/[entity]/edit.blade.php`

### 2.6 ACL
- Config: `packages/Webkul/[Module]/src/Config/acl.php`
- Menu: `packages/Webkul/[Module]/src/Config/admin-menu.php`

### 2.7 Lang
- Lang: `packages/Webkul/[Module]/src/Resources/lang/en/app.php`
- Lang TR: `packages/Webkul/[Module]/src/Resources/lang/tr/app.php`

### 🚦 Approval Gate 1
> **Architect planı tamamlandığında Orchestrator onayı alınır.**

---

## Adım 3: Implementasyon (Implementer)

**Skill:** Implementer

**Küçük Patch'ler Halinde:**

### Patch 3.1: Model ve Repository
- [ ] Model oluştur
- [ ] Repository oluştur
- [ ] Service Provider'da kayıt

### Patch 3.2: Controller ve Request
- [ ] Controller oluştur (RESTful methods)
- [ ] Form Request oluştur

### Patch 3.3: Routes
- [ ] Route tanımları ekle
- [ ] Middleware ayarları

### 🚦 Approval Gate 2 (ACL için)
> **ACL değişikliği öncesi Orchestrator onayı alınır.**

### Patch 3.4: ACL ve Menu
- [ ] ACL config güncelle
- [ ] Admin menu güncelle

### Patch 3.5: Datagrid
- [ ] Datagrid class oluştur
- [ ] Column tanımları
- [ ] Filter ve Action'lar

### Patch 3.6: Views
- [ ] Index view (datagrid)
- [ ] Create form
- [ ] Edit form

### Patch 3.7: Lang Dosyaları
- [ ] English translations
- [ ] Turkish translations

---

## Adım 4: Test (Tester)

**Skill:** Tester

**Test Listesi:**
- [ ] CRUD işlemleri çalışıyor mu?
- [ ] Validation çalışıyor mu?
- [ ] ACL kontrolleri çalışıyor mu?
- [ ] Datagrid filtering/sorting çalışıyor mu?

**Komutlar:**
```bash
php artisan test --filter=[EntityTest]
```

---

## Adım 5: Review (Reviewer)

**Skill:** Reviewer

**Kontrol Listesi:**
- [ ] PSR-12 uyumu
- [ ] Bagisto patterns kullanımı
- [ ] Security kontrolleri (validation, auth)
- [ ] Performans (N+1 query yok)
- [ ] ACL doğru tanımlanmış

### 🚦 Approval Gate 3
> **Reviewer GO/NO-GO kararı verir.**

---

## Adım 6: Dokümantasyon (Doc-Writer)

**Skill:** Doc-Writer

**Çıktılar:**
- PR açıklaması
- Changelog girişi

---

## Dosya Şablonları

### Controller Şablonu

```php
namespace Webkul\[Module]\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\[Module]\DataGrids\[Entity]DataGrid;
use Webkul\[Module]\Repositories\[Entity]Repository;

class [Entity]Controller extends Controller
{
    public function __construct(
        protected [Entity]Repository $[entity]Repository
    ) {
    }

    public function index(): View
    {
        if (request()->ajax()) {
            return app([Entity]DataGrid::class)->toJson();
        }

        return view('[module]::admin.[entity].index');
    }

    public function create(): View
    {
        return view('[module]::admin.[entity].create');
    }

    public function store([Entity]Request $request): RedirectResponse
    {
        $this->[entity]Repository->create($request->validated());

        session()->flash('success', trans('[module]::app.[entity].success.create'));

        return redirect()->route('admin.[module].[entity].index');
    }

    public function edit(int $id): View
    {
        $[entity] = $this->[entity]Repository->findOrFail($id);

        return view('[module]::admin.[entity].edit', compact('[entity]'));
    }

    public function update([Entity]Request $request, int $id): RedirectResponse
    {
        $this->[entity]Repository->update($request->validated(), $id);

        session()->flash('success', trans('[module]::app.[entity].success.update'));

        return redirect()->route('admin.[module].[entity].index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->[entity]Repository->delete($id);

        session()->flash('success', trans('[module]::app.[entity].success.delete'));

        return redirect()->route('admin.[module].[entity].index');
    }
}
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [patch-policy.md](../policies/patch-policy.md)
- [php-laravel-style.md](../policies/php-laravel-style.md)
- [laravel-bagisto-change-checklist.md](../checklists/laravel-bagisto-change-checklist.md)
