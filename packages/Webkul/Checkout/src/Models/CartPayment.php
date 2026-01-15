<?php

namespace Webkul\Checkout\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantScopedConnection;
use Webkul\Checkout\Contracts\CartPayment as CartPaymentContract;
use Webkul\Checkout\Database\Factories\CartPaymentFactory;

class CartPayment extends Model implements CartPaymentContract
{
    use HasFactory, TenantScopedConnection;

    protected $table = 'cart_payment';

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): Factory
    {
        return CartPaymentFactory::new();
    }
}
