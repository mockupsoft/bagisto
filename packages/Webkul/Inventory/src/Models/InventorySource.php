<?php

namespace Webkul\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantScopedConnection;
use Webkul\Inventory\Contracts\InventorySource as InventorySourceContract;
use Webkul\Inventory\Database\Factories\InventorySourceFactory;

class InventorySource extends Model implements InventorySourceContract
{
    use HasFactory, TenantScopedConnection;

    protected $guarded = ['_token'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return InventorySourceFactory::new();
    }
}
