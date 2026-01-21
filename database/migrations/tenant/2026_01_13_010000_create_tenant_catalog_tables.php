<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('attribute_families')) {
            Schema::create('attribute_families', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->boolean('status')->default(0);
                $table->boolean('is_user_defined')->default(1);
            });
        }

        if (! Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('admin_name');
                $table->string('type');
                $table->string('swatch_type')->nullable();
                $table->string('validation')->nullable();
                $table->integer('position')->nullable();
                $table->boolean('is_required')->default(0);
                $table->boolean('is_unique')->default(0);
                $table->boolean('is_filterable')->default(0);
                $table->boolean('is_comparable')->default(0);
                $table->boolean('is_configurable')->default(0);
                $table->boolean('is_user_defined')->default(1);
                $table->boolean('is_visible_on_front')->default(0);
                $table->boolean('value_per_locale')->default(0);
                $table->boolean('value_per_channel')->default(0);
                $table->boolean('enable_wysiwyg')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attribute_translations')) {
            Schema::create('attribute_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('attribute_id')->unsigned();
                $table->string('locale');
                $table->text('name')->nullable();

                $table->unique(['attribute_id', 'locale']);
                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('attribute_options')) {
            Schema::create('attribute_options', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('attribute_id')->unsigned();
                $table->string('admin_name')->nullable();
                $table->integer('sort_order')->nullable();
                $table->string('swatch_value')->nullable();

                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('attribute_option_translations')) {
            Schema::create('attribute_option_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('attribute_option_id')->unsigned();
                $table->string('locale');
                $table->text('label')->nullable();

                $table->unique(['attribute_option_id', 'locale'], 'attribute_option_locale_unique');
                $table->foreign('attribute_option_id')->references('id')->on('attribute_options')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('attribute_groups')) {
            Schema::create('attribute_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('attribute_family_id')->unsigned();
                $table->string('name');
                $table->integer('position');
                $table->boolean('is_user_defined')->default(1);

                $table->unique(['attribute_family_id', 'name']);
                $table->foreign('attribute_family_id')->references('id')->on('attribute_families')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('attribute_group_mappings')) {
            Schema::create('attribute_group_mappings', function (Blueprint $table) {
                $table->integer('attribute_id')->unsigned();
                $table->integer('attribute_group_id')->unsigned();
                $table->integer('position')->nullable();

                $table->primary(['attribute_id', 'attribute_group_id']);
                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
                $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('position')->default(0);
                $table->boolean('status')->default(0);
                $table->string('display_mode')->default('products_and_description')->nullable();
                $table->unsignedInteger('_lft')->default(0);
                $table->unsignedInteger('_rgt')->default(0);
                $table->unsignedInteger('parent_id')->nullable();
                $table->index(['_lft', '_rgt', 'parent_id']);
                $table->json('additional')->nullable();
                $table->text('logo_path')->nullable();
                $table->text('banner_path')->nullable();
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('category_translations')) {
            Schema::create('category_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id')->unsigned();
                $table->text('name');
                $table->string('slug');
                $table->string('url_path', 2048)->nullable();
                $table->text('description')->nullable();
                $table->text('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->integer('locale_id')->nullable()->unsigned();
                $table->string('locale');

                $table->unique(['category_id', 'slug', 'locale']);
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('locales')) {
            Schema::create('locales', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->string('symbol');
                $table->decimal('exchange_rate', 12, 4)->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code');
                $table->string('timezone')->nullable();
                $table->string('theme')->nullable();
                $table->string('hostname')->nullable();
                $table->string('logo')->nullable();
                $table->string('favicon')->nullable();
                $table->json('home_seo')->nullable();
                $table->boolean('is_maintenance_on')->default(0);
                $table->text('allowed_ips')->nullable();
                $table->integer('root_category_id')->nullable()->unsigned();
                $table->integer('default_locale_id')->unsigned();
                $table->integer('base_currency_id')->unsigned();
                $table->timestamps();

                $table->foreign('root_category_id')->references('id')->on('categories')->onDelete('set null');
                $table->foreign('default_locale_id')->references('id')->on('locales');
                $table->foreign('base_currency_id')->references('id')->on('currencies');
            });
        }

        if (! Schema::hasTable('channel_locales')) {
            Schema::create('channel_locales', function (Blueprint $table) {
                $table->integer('channel_id')->unsigned();
                $table->integer('locale_id')->unsigned();

                $table->primary(['channel_id', 'locale_id']);
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
                $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('channel_currencies')) {
            Schema::create('channel_currencies', function (Blueprint $table) {
                $table->integer('channel_id')->unsigned();
                $table->integer('currency_id')->unsigned();

                $table->primary(['channel_id', 'currency_id']);
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('channel_translations')) {
            Schema::create('channel_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('channel_id')->unsigned();
                $table->string('locale')->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->text('home_page_content')->nullable();
                $table->text('footer_content')->nullable();
                $table->text('maintenance_mode_text')->nullable();
                $table->json('home_seo')->nullable();
                $table->timestamps();

                $table->unique(['channel_id', 'locale']);
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sku')->unique();
                $table->string('type');
                $table->integer('parent_id')->unsigned()->nullable();
                $table->integer('attribute_family_id')->unsigned()->nullable();
                $table->json('additional')->nullable();
                $table->timestamps();

                $table->foreign('attribute_family_id')->references('id')->on('attribute_families')->onDelete('restrict');
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'parent_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('parent_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->integer('product_id')->unsigned();
                $table->integer('category_id')->unsigned();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->increments('id');
                $table->string('locale')->nullable();
                $table->string('channel')->nullable();
                $table->text('text_value')->nullable();
                $table->boolean('boolean_value')->nullable();
                $table->integer('integer_value')->nullable();
                $table->decimal('float_value', 12, 4)->nullable();
                $table->decimal('decimal_value', 12, 4)->nullable();
                $table->dateTime('datetime_value')->nullable();
                $table->date('date_value')->nullable();
                $table->json('json_value')->nullable();
                $table->integer('product_id')->unsigned();
                $table->integer('attribute_id')->unsigned();
                $table->string('unique_id')->nullable()->unique();

                $table->unique(['channel', 'locale', 'attribute_id', 'product_id'], 'chanel_locale_attribute_value_index_unique');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_flat')) {
            Schema::create('product_flat', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sku');
                $table->string('type')->nullable();
                $table->string('product_number')->nullable();
                $table->string('name')->nullable();
                $table->text('short_description')->nullable();
                $table->text('description')->nullable();
                $table->string('url_key')->nullable();
                $table->boolean('new')->nullable();
                $table->boolean('featured')->nullable();
                $table->boolean('status')->nullable();
                $table->text('meta_title')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->text('meta_description')->nullable();
                $table->decimal('price', 12, 4)->nullable();
                $table->decimal('special_price', 12, 4)->nullable();
                $table->date('special_price_from')->nullable();
                $table->date('special_price_to')->nullable();
                $table->decimal('weight', 12, 4)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('locale')->nullable();
                $table->string('channel')->nullable();
                $table->integer('attribute_family_id')->unsigned()->nullable();
                $table->integer('product_id')->unsigned();
                $table->dateTime('updated_at')->nullable();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->boolean('visible_individually')->nullable();

                $table->unique(['product_id', 'channel', 'locale'], 'product_flat_unique_index');
                $table->foreign('attribute_family_id')->references('id')->on('attribute_families')->onDelete('restrict');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on('product_flat')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_channels')) {
            Schema::create('product_channels', function (Blueprint $table) {
                $table->integer('product_id')->unsigned();
                $table->integer('channel_id')->unsigned();

                $table->unique(['product_id', 'channel_id']);
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type')->nullable();
                $table->string('path');
                $table->integer('product_id')->unsigned();
                $table->integer('position')->default(0)->unsigned();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('product_videos')) {
            Schema::create('product_videos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id')->unsigned();
                $table->string('type')->nullable();
                $table->string('path');
                $table->integer('position')->default(0)->unsigned();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_videos');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_channels');
        Schema::dropIfExists('product_flat');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('channel_translations');
        Schema::dropIfExists('channel_currencies');
        Schema::dropIfExists('channel_locales');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('locales');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('attribute_option_translations');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attribute_translations');
        Schema::dropIfExists('attribute_group_mappings');
        Schema::dropIfExists('attribute_groups');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_families');
    }
};
