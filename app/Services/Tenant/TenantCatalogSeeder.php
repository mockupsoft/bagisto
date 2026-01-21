<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantCatalogSeeder
{
    public function seed(): void
    {
        $schema = Schema::connection('tenant');

        if (! $schema->hasTable('categories') || ! $schema->hasTable('category_translations')) {
            return;
        }

        $conn = DB::connection('tenant');
        $now = now();
        $locale = config('app.locale', 'en');

        $categoryId = $conn->table('category_translations')
            ->where('locale', $locale)
            ->where('slug', 'default')
            ->value('category_id');

        if (! $categoryId) {
            $categoryId = $conn->table('categories')->insertGetId([
                'parent_id' => null,
                'status' => 1,
                'position' => 1,
                'display_mode' => 'products_and_description',
                '_lft' => 1,
                '_rgt' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $conn->table('category_translations')->insert([
                'category_id' => $categoryId,
                'locale' => $locale,
                'name' => 'Default',
                'slug' => 'default',
                'url_path' => 'default',
            ]);
        }

        if (! $schema->hasTable('attributes')) {
            return;
        }

        $attributeId = $conn->table('attributes')->where('code', 'name')->value('id');

        if (! $attributeId) {
            $conn->table('attributes')->insert([
                'code' => 'name',
                'admin_name' => 'Name',
                'type' => 'text',
                'is_required' => 1,
                'is_unique' => 0,
                'is_filterable' => 0,
                'is_comparable' => 0,
                'is_configurable' => 0,
                'is_user_defined' => 0,
                'is_visible_on_front' => 0,
                'value_per_locale' => 1,
                'value_per_channel' => 0,
                'enable_wysiwyg' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
