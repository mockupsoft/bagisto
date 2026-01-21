<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('inventory_sources')) {
            Schema::create('inventory_sources', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('contact_name');
                $table->string('contact_email');
                $table->string('contact_number');
                $table->string('contact_fax')->nullable();
                $table->string('country');
                $table->string('state');
                $table->string('city');
                $table->string('street');
                $table->string('postcode');
                $table->integer('priority')->default(0);
                $table->decimal('latitude', 10, 5)->nullable();
                $table->decimal('longitude', 10, 5)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_inventories')) {
            Schema::create('product_inventories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('qty')->default(0);
                $table->unsignedBigInteger('product_id');
                $table->integer('vendor_id')->default(0);
                $table->unsignedInteger('inventory_source_id');

                $table->unique(['product_id', 'inventory_source_id', 'vendor_id'], 'product_source_vendor_index_unique');

                $table->index('product_id');
                $table->index('inventory_source_id');

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_ordered_inventories')) {
            Schema::create('product_ordered_inventories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('qty')->default(0);
                $table->unsignedBigInteger('product_id');
                $table->unsignedInteger('channel_id');

                $table->unique(['product_id', 'channel_id']);

                $table->index('product_id');
                $table->index('channel_id');

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_inventory_indices')) {
            Schema::create('product_inventory_indices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('qty')->default(0);
                $table->unsignedBigInteger('product_id');
                $table->unsignedInteger('channel_id');
                $table->timestamps();

                $table->unique(['product_id', 'channel_id']);

                $table->index('product_id');
                $table->index('channel_id');

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventory_indices');
        Schema::dropIfExists('product_ordered_inventories');
        Schema::dropIfExists('product_inventories');
        Schema::dropIfExists('inventory_sources');
    }
};
