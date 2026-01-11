# Laravel/Bagisto Değişiklik Kontrol Listesi

Her değişiklik öncesi ve sonrası bu listeyi kontrol edin.

---

## Değişiklik Öncesi

### Genel Hazırlık

- [ ] Mevcut branch güncel mi?
- [ ] Local environment çalışıyor mu?
- [ ] Database backup alındı mı? (önemli değişiklikler için)

### Scope Belirleme

- [ ] Değişiklik kapsamı net mi?
- [ ] Etkilenecek modüller belirlendi mi?
- [ ] Risk analizi yapıldı mı?

---

## Değişiklik Sonrası

### Migration Kontrolü

- [ ] Migration dosyası oluşturuldu mu?
- [ ] `up()` method doğru mu?
- [ ] `down()` method reversible mi?
- [ ] Rollback test edildi mi?

```bash
# Migration test
php artisan migrate
php artisan migrate:rollback
php artisan migrate
```

### Config/Cache Etkileri

- [ ] Yeni config dosyası eklendi mi?
- [ ] Config cache temizlenmeli mi?
- [ ] Route cache temizlenmeli mi?
- [ ] View cache temizlenmeli mi?

```bash
# Cache temizleme
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Permissions/ACL Güncellemeleri

- [ ] Yeni permission eklendi mi?
- [ ] `acl.php` config güncellendi mi?
- [ ] Route middleware eklendi mi?
- [ ] Admin menu güncellendi mi?

### Lang Dosyaları

- [ ] Yeni translation key'leri eklendi mi?
- [ ] Tüm desteklenen diller güncellendi mi?
- [ ] Key naming convention'a uygun mu?

```
Resources/lang/
├── en/
│   └── app.php  ✅
├── tr/
│   └── app.php  ✅
└── ...
```

### Datagrid Güncellemeleri

- [ ] Yeni column eklendi mi?
- [ ] Filter tanımları doğru mu?
- [ ] Action'lar çalışıyor mu?
- [ ] Mass action gerekli mi?

### Events/Listeners Etkileri

- [ ] Yeni event oluşturuldu mu?
- [ ] Listener eklendi mi?
- [ ] Service Provider'da kayıt yapıldı mı?
- [ ] Mevcut event'lere etki var mı?

### Database Index/Performans

- [ ] Gerekli index'ler eklendi mi?
- [ ] N+1 query riski kontrol edildi mi?
- [ ] Eager loading kullanıldı mı?
- [ ] Büyük data set'ler için pagination var mı?

```php
// Index örneği (migration içinde)
$table->index('status');
$table->index(['category_id', 'status']);
```

### Test Çalıştırma

- [ ] Unit testler çalıştırıldı mı?
- [ ] Feature testler çalıştırıldı mı?
- [ ] Yeni test yazıldı mı? (gerekiyorsa)
- [ ] Test sonuçları kaydedildi mi?

```bash
# Test komutları
php artisan test
php artisan test --filter=SpecificTest
php artisan test --coverage
```

---

## Sonuç Kaydı

```markdown
### Test Sonuçları

| Kategori | Sonuç |
|----------|-------|
| Unit Tests | ✅ X passed / ❌ Y failed |
| Feature Tests | ✅ X passed / ❌ Y failed |
| Total | X tests |

### Checklist Durumu

| Kontrol | Durum |
|---------|-------|
| Migration | ✅/❌/N/A |
| Config/Cache | ✅/❌/N/A |
| ACL | ✅/❌/N/A |
| Lang | ✅/❌/N/A |
| Datagrid | ✅/❌/N/A |
| Events | ✅/❌/N/A |
| Performance | ✅/❌/N/A |
| Tests | ✅/❌/N/A |

### Notlar
[Ek notlar]
```

---

## Hızlı Komut Referansı

```bash
# Migration
php artisan make:migration create_examples_table
php artisan migrate
php artisan migrate:rollback
php artisan migrate:status

# Cache
php artisan optimize:clear

# Test
php artisan test

# Code style (PHP CS Fixer varsa)
./vendor/bin/php-cs-fixer fix

# Static analysis (PHPStan varsa)
./vendor/bin/phpstan analyse
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [patch-policy.md](../policies/patch-policy.md)
- [tester.md](../skills/tester.md)
- [reviewer.md](../skills/reviewer.md)
