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

        // Root category
        $rootId = $conn->table('categories')->whereNull('parent_id')->value('id');

        if (! $rootId) {
            $rootId = $conn->table('categories')->insertGetId([
                'parent_id' => null,
                'status' => true,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Root category translation (en)
        $hasTranslation = $conn->table('category_translations')
            ->where('category_id', $rootId)
            ->where('locale', config('app.locale', 'en'))
            ->exists();

        if (! $hasTranslation) {
            $conn->table('category_translations')->insert([
                'category_id' => $rootId,
                'locale' => config('app.locale', 'en'),
                'name' => 'Root Category',
                'slug' => 'root-category',
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
