# PHP/Laravel/Bagisto Stil Rehberi

Bu politika, kod stilini ve convention'ları tanımlar.

---

## PSR-12 Standartları

### Temel Kurallar

- **Indentation:** 4 space (tab değil)
- **Line length:** Maksimum 120 karakter
- **Line ending:** LF (Unix style)
- **File ending:** Tek boş satır ile biter
- **PHP tag:** Sadece `<?php` (kısa tag yok)

### Namespace ve Use

```php
<?php

namespace Webkul\ModuleName\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Core\Http\Controllers\Controller;
use Webkul\ModuleName\Repositories\ExampleRepository;

class ExampleController extends Controller
{
    // ...
}
```

### Class Yapısı

```php
class ExampleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(
        protected ExampleRepository $exampleRepository
    ) {
    }

    /**
     * Display a listing.
     */
    public function index(): View
    {
        // ...
    }
}
```

### Method Yapısı

- Tek boş satır method'lar arasında
- Return type declaration kullan
- PHPDoc yalnızca gerektiğinde

```php
/**
 * Store a newly created resource.
 */
public function store(StoreRequest $request): RedirectResponse
{
    $this->exampleRepository->create($request->validated());

    session()->flash('success', trans('admin::app.success'));

    return redirect()->route('admin.example.index');
}
```

---

## Laravel Conventions

### Naming Conventions

| Tür | Convention | Örnek |
|-----|------------|-------|
| Controller | Singular, PascalCase | `ProductController` |
| Model | Singular, PascalCase | `Product` |
| Table | Plural, snake_case | `products` |
| Migration | Descriptive, snake_case | `create_products_table` |
| Method | camelCase | `getActiveProducts()` |
| Variable | camelCase | `$activeProducts` |
| Config/Lang key | snake_case | `app.admin_email` |
| Route name | kebab-case veya dot.notation | `admin.products.index` |

### Controller Methods

RESTful convention:

| Method | URI | Action |
|--------|-----|--------|
| GET | /products | index |
| GET | /products/create | create |
| POST | /products | store |
| GET | /products/{id} | show |
| GET | /products/{id}/edit | edit |
| PUT/PATCH | /products/{id} | update |
| DELETE | /products/{id} | destroy |

### Request Validation

Form Request kullanımı tercih edilir:

```php
// app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

### Eloquent Best Practices

```php
// Eager loading
$products = Product::with('categories', 'images')->get();

// Scopes
public function scopeActive($query)
{
    return $query->where('status', 1);
}

// Accessors (Laravel 9+)
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}",
    );
}
```

---

## Bagisto Conventions

### Modül Yapısı

```
packages/Webkul/[ModuleName]/
├── src/
│   ├── Config/
│   │   ├── acl.php
│   │   ├── menu.php
│   │   └── system.php
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeders/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   └── Shop/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Repositories/
│   ├── Resources/
│   │   ├── lang/
│   │   │   ├── en/
│   │   │   └── tr/
│   │   └── views/
│   │       ├── admin/
│   │       └── shop/
│   ├── Routes/
│   │   ├── admin-routes.php
│   │   └── shop-routes.php
│   └── Providers/
│       └── ModuleNameServiceProvider.php
└── composer.json
```

### Repository Pattern

```php
namespace Webkul\ModuleName\Repositories;

use Webkul\Core\Eloquent\Repository;

class ExampleRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return \Webkul\ModuleName\Models\Example::class;
    }

    /**
     * Custom method.
     */
    public function getActiveItems()
    {
        return $this->model->where('status', 1)->get();
    }
}
```

### Service Provider

```php
namespace Webkul\ModuleName\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleNameServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'modulename');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'modulename');
        
        $this->app->register(EventServiceProvider::class);
    }

    public function register(): void
    {
        $this->registerConfig();
    }
}
```

### Datagrid Pattern

```php
namespace Webkul\ModuleName\DataGrids;

use Webkul\DataGrid\DataGrid;

class ExampleDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    public function prepareQueryBuilder(): void
    {
        $queryBuilder = DB::table('examples')
            ->select('id', 'name', 'status', 'created_at');

        $this->setQueryBuilder($queryBuilder);
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('modulename::app.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'title' => trans('ui::app.datagrid.edit'),
            'method' => 'GET',
            'route' => 'admin.example.edit',
            'icon' => 'icon-edit',
        ]);
    }
}
```

### Lang Dosyaları

```php
// Resources/lang/en/app.php
return [
    'module-name' => [
        'title' => 'Module Title',
        'create' => 'Create New',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'success' => [
            'create' => 'Created successfully.',
            'update' => 'Updated successfully.',
            'delete' => 'Deleted successfully.',
        ],
    ],
];
```

### Admin Menu

```php
// Config/admin-menu.php
return [
    [
        'key' => 'modulename',
        'name' => 'modulename::app.title',
        'route' => 'admin.modulename.index',
        'sort' => 5,
        'icon' => 'icon-settings',
    ],
];
```

---

## Blade Template Conventions

### Component Kullanımı

```blade
{{-- Bagisto component --}}
<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        {{ __('Name') }}
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="text"
        name="name"
        :value="old('name')"
    />
</x-admin::form.control-group>
```

### Layout Kullanımı

```blade
<x-admin::layouts>
    <x-slot:title>
        {{ __('Page Title') }}
    </x-slot>

    <div class="content">
        {{-- Content --}}
    </div>
</x-admin::layouts>
```

---

## İlgili Dosyalar

- [AGENTS.md](../../AGENTS.md)
- [implementer.md](../skills/implementer.md)
- [reviewer.md](../skills/reviewer.md)
