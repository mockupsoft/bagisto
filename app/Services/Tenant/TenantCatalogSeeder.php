<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
class TenantCatalogSeeder
{
    public function seed(): void
    {
        $conn = DB::connection('tenant');

        // Default category with nested-set values
        $defaultCategoryId = $conn->table('categories')
            ->whereNull('parent_id')
            ->value('id');

        if (! $defaultCategoryId) {
            $defaultCategoryId = $conn->table('categories')->insertGetId([
                'parent_id' => null,
                '_lft' => 1,
                '_rgt' => 2,
                'depth' => 0,
                'status' => true,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Default category translation (en)
        $hasTranslation = $conn->table('category_translations')
            ->where('category_id', $defaultCategoryId)
            ->where('locale', config('app.locale', 'en'))
            ->exists();

        if (! $hasTranslation) {
            $conn->table('category_translations')->insert([
                'category_id' => $defaultCategoryId,
                'locale' => config('app.locale', 'en'),
                'name' => 'Default',
                'slug' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Default attribute family
        $hasFamily = $conn->table('attribute_families')->where('code', 'default')->exists();

        if (! $hasFamily) {
            $conn->table('attribute_families')->insert([
                'code' => 'default',
                'name' => 'Default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
