<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_families', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('locale');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'locale']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('simple');
            $table->unsignedBigInteger('attribute_family_id')->nullable();
            $table->string('sku')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_code')->nullable();
            $table->string('locale')->nullable();
            $table->string('channel')->nullable();
            $table->text('text_value')->nullable();
            $table->decimal('decimal_value', 12, 4)->nullable();
            $table->integer('integer_value')->nullable();
            $table->datetime('datetime_value')->nullable();
            $table->date('date_value')->nullable();
            $table->text('json_value')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'attribute_code']);
        });

        Schema::create('product_flat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->index();
            $table->string('name')->nullable();
            $table->decimal('price', 12, 4)->nullable();
            $table->boolean('status')->default(true);
            $table->string('url_key')->nullable();
            $table->string('locale')->nullable();
            $table->string('channel')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'locale', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_flat');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('products');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('attribute_families');
    }
};
