<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('cart')) {
            Schema::create('cart', function (Blueprint $table) {
                $table->increments('id');
                $table->string('customer_email')->nullable();
                $table->string('customer_first_name')->nullable();
                $table->string('customer_last_name')->nullable();
                $table->string('shipping_method')->nullable();
                $table->string('coupon_code')->nullable();
                $table->boolean('is_gift')->default(false);
                $table->integer('items_count')->nullable();
                $table->decimal('items_qty', 12, 4)->nullable();
                $table->decimal('exchange_rate', 12, 4)->nullable();
                $table->string('global_currency_code')->nullable();
                $table->string('base_currency_code')->nullable();
                $table->string('channel_currency_code')->nullable();
                $table->string('cart_currency_code')->nullable();
                $table->decimal('grand_total', 12, 4)->default(0)->nullable();
                $table->decimal('base_grand_total', 12, 4)->default(0)->nullable();
                $table->decimal('sub_total', 12, 4)->default(0)->nullable();
                $table->decimal('base_sub_total', 12, 4)->default(0)->nullable();
                $table->decimal('tax_total', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_total', 12, 4)->default(0)->nullable();
                $table->decimal('discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_amount', 12, 4)->default(0)->nullable();
                $table->string('checkout_method')->nullable();
                $table->boolean('is_guest')->nullable();
                $table->boolean('is_active')->nullable()->default(true);
                $table->string('applied_cart_rule_ids')->nullable();

                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedInteger('channel_id')->nullable();
                $table->timestamps();

                $table->index('customer_id');
                $table->index('channel_id');
            });
        }

        if (! Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('quantity')->unsigned()->default(0);
                $table->string('sku')->nullable();
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('coupon_code')->nullable();
                $table->decimal('weight', 12, 4)->default(0);
                $table->decimal('total_weight', 12, 4)->default(0);
                $table->decimal('base_total_weight', 12, 4)->default(0);

                $table->decimal('price', 12, 4)->default(0);
                $table->decimal('base_price', 12, 4)->default(0);
                $table->decimal('custom_price', 12, 4)->nullable();
                $table->decimal('total', 12, 4)->default(0);
                $table->decimal('base_total', 12, 4)->default(0);

                $table->decimal('tax_percent', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount', 12, 4)->default(0)->nullable();

                $table->decimal('discount_percent', 12, 4)->default(0);
                $table->decimal('discount_amount', 12, 4)->default(0);
                $table->decimal('base_discount_amount', 12, 4)->default(0);

                // incl tax columns (Bagisto 2.x)
                $table->decimal('price_incl_tax', 12, 4)->default(0);
                $table->decimal('base_price_incl_tax', 12, 4)->default(0);
                $table->decimal('total_incl_tax', 12, 4)->default(0);
                $table->decimal('base_total_incl_tax', 12, 4)->default(0);
                $table->string('applied_tax_rate')->nullable();

                $table->unsignedInteger('parent_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedInteger('cart_id');
                $table->unsignedInteger('tax_category_id')->nullable();
                $table->string('applied_cart_rule_ids')->nullable();
                $table->json('additional')->nullable();
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('cart_items')->onDelete('cascade');
                $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');

                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('cart_payment')) {
            Schema::create('cart_payment', function (Blueprint $table) {
                $table->increments('id');
                $table->string('method');
                $table->string('method_title')->nullable();
                $table->unsignedInteger('cart_id')->nullable();
                $table->timestamps();

                $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('cart_shipping_rates')) {
            Schema::create('cart_shipping_rates', function (Blueprint $table) {
                $table->increments('id');
                $table->string('carrier');
                $table->string('carrier_title');
                $table->string('method');
                $table->string('method_title');
                $table->string('method_description')->nullable();
                $table->double('price')->default(0)->nullable();
                $table->double('base_price')->default(0)->nullable();
                $table->decimal('discount_amount', 12, 4)->default(0);
                $table->decimal('base_discount_amount', 12, 4)->default(0);

                // incl tax columns (Bagisto 2.x)
                $table->decimal('tax_percent', 12, 4)->default(0);
                $table->decimal('tax_amount', 12, 4)->default(0);
                $table->decimal('base_tax_amount', 12, 4)->default(0);
                $table->decimal('price_incl_tax', 12, 4)->default(0);
                $table->decimal('base_price_incl_tax', 12, 4)->default(0);
                $table->string('applied_tax_rate')->nullable();

                $table->boolean('is_calculate_tax')->default(true);
                $table->unsignedInteger('cart_address_id')->nullable();
                $table->unsignedInteger('cart_id')->nullable();
                $table->timestamps();

                $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');

                $table->index('cart_address_id');
            });
        }

        if (! Schema::hasTable('cart_item_inventories')) {
            Schema::create('cart_item_inventories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('qty')->unsigned()->default(0);
                $table->unsignedInteger('inventory_source_id')->nullable();
                $table->unsignedInteger('cart_item_id')->nullable();
                $table->timestamps();

                $table->index('inventory_source_id');
                $table->index('cart_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_item_inventories');
        Schema::dropIfExists('cart_shipping_rates');
        Schema::dropIfExists('cart_payment');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('cart');
    }
};
