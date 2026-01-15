<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->string('increment_id')->unique();
                $table->string('status')->nullable();
                $table->string('channel_name')->nullable();
                $table->boolean('is_guest')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_first_name')->nullable();
                $table->string('customer_last_name')->nullable();
                $table->string('shipping_method')->nullable();
                $table->string('shipping_title')->nullable();
                $table->string('shipping_description')->nullable();
                $table->string('coupon_code')->nullable();
                $table->boolean('is_gift')->default(false);
                $table->integer('total_item_count')->nullable();
                $table->integer('total_qty_ordered')->nullable();
                $table->string('base_currency_code')->nullable();
                $table->string('channel_currency_code')->nullable();
                $table->string('order_currency_code')->nullable();

                $table->decimal('grand_total', 12, 4)->default(0)->nullable();
                $table->decimal('base_grand_total', 12, 4)->default(0)->nullable();

                $table->decimal('grand_total_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_grand_total_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('grand_total_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_grand_total_refunded', 12, 4)->default(0)->nullable();

                $table->decimal('sub_total', 12, 4)->default(0)->nullable();
                $table->decimal('base_sub_total', 12, 4)->default(0)->nullable();
                $table->decimal('sub_total_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_sub_total_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('sub_total_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_sub_total_refunded', 12, 4)->default(0)->nullable();

                $table->decimal('discount_percent', 12, 4)->default(0)->nullable();
                $table->decimal('discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('discount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('discount_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_refunded', 12, 4)->default(0)->nullable();

                $table->decimal('tax_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount_refunded', 12, 4)->default(0)->nullable();

                $table->decimal('shipping_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_shipping_amount', 12, 4)->default(0)->nullable();
                $table->decimal('shipping_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_shipping_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('shipping_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_shipping_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('shipping_discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_shipping_discount_amount', 12, 4)->default(0)->nullable();

                // incl tax columns (Bagisto 2.x)
                $table->decimal('shipping_tax_amount', 12, 4)->default(0);
                $table->decimal('base_shipping_tax_amount', 12, 4)->default(0);
                $table->decimal('shipping_tax_refunded', 12, 4)->default(0);
                $table->decimal('base_shipping_tax_refunded', 12, 4)->default(0);
                $table->decimal('sub_total_incl_tax', 12, 4)->default(0);
                $table->decimal('base_sub_total_incl_tax', 12, 4)->default(0);
                $table->decimal('shipping_amount_incl_tax', 12, 4)->default(0);
                $table->decimal('base_shipping_amount_incl_tax', 12, 4)->default(0);

                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_type')->nullable();
                $table->unsignedInteger('channel_id')->nullable();
                $table->string('channel_type')->nullable();
                $table->unsignedInteger('cart_id')->nullable();
                $table->string('applied_cart_rule_ids')->nullable();
                $table->timestamps();

                $table->index('customer_id');
                $table->index('channel_id');
                $table->index('cart_id');
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sku')->nullable();
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('coupon_code')->nullable();
                $table->decimal('weight', 12, 4)->default(0)->nullable();
                $table->decimal('total_weight', 12, 4)->default(0)->nullable();
                $table->integer('qty_ordered')->default(0)->nullable();
                $table->integer('qty_shipped')->default(0)->nullable();
                $table->integer('qty_invoiced')->default(0)->nullable();
                $table->integer('qty_canceled')->default(0)->nullable();
                $table->integer('qty_refunded')->default(0)->nullable();

                $table->decimal('price', 12, 4)->default(0);
                $table->decimal('base_price', 12, 4)->default(0);
                $table->decimal('total', 12, 4)->default(0);
                $table->decimal('base_total', 12, 4)->default(0);

                $table->decimal('total_invoiced', 12, 4)->default(0);
                $table->decimal('base_total_invoiced', 12, 4)->default(0);
                $table->decimal('amount_refunded', 12, 4)->default(0);
                $table->decimal('base_amount_refunded', 12, 4)->default(0);

                $table->decimal('discount_percent', 12, 4)->default(0)->nullable();
                $table->decimal('discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_amount', 12, 4)->default(0)->nullable();
                $table->decimal('discount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('discount_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_discount_refunded', 12, 4)->default(0)->nullable();

                $table->decimal('tax_percent', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount_invoiced', 12, 4)->default(0)->nullable();
                $table->decimal('tax_amount_refunded', 12, 4)->default(0)->nullable();
                $table->decimal('base_tax_amount_refunded', 12, 4)->default(0)->nullable();

                // incl tax columns (Bagisto 2.x)
                $table->decimal('price_incl_tax', 12, 4)->default(0);
                $table->decimal('base_price_incl_tax', 12, 4)->default(0);
                $table->decimal('total_incl_tax', 12, 4)->default(0);
                $table->decimal('base_total_incl_tax', 12, 4)->default(0);

                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_type')->nullable();
                $table->unsignedInteger('order_id')->nullable();
                $table->unsignedInteger('parent_id')->nullable();
                $table->json('additional')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on('order_items')->onDelete('cascade');

                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('order_payment')) {
            Schema::create('order_payment', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('order_id')->nullable();
                $table->string('method');
                $table->string('method_title')->nullable();
                $table->json('additional')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
