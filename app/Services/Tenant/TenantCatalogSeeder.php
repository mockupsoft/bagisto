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

        // Default channel (required for product creation)
        $hasChannel = $conn->table('channels')->where('code', 'default')->exists();

        if (! $hasChannel) {
            // Get or create locale
            $localeId = $conn->table('locales')->where('code', config('app.locale', 'en'))->value('id');
            if (! $localeId) {
                $localeId = $conn->table('locales')->insertGetId([
                    'code' => config('app.locale', 'en'),
                    'name' => 'English',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Get or create currency
            $currencyId = $conn->table('currencies')->where('code', 'USD')->value('id');
            if (! $currencyId) {
                $currencyId = $conn->table('currencies')->insertGetId([
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'symbol' => '$',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create default channel
            $channelId = $conn->table('channels')->insertGetId([
                'code' => 'default',
                'name' => 'Default',
                'description' => 'Default Channel',
                'hostname' => 'localhost',
                'theme' => 'default',
                'root_category_id' => $rootId,
                'default_locale_id' => $localeId,
                'base_currency_id' => $currencyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link channel to locale
            $conn->table('channel_locales')->insert([
                'channel_id' => $channelId,
                'locale_id' => $localeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link channel to currency
            $conn->table('channel_currencies')->insert([
                'channel_id' => $channelId,
                'currency_id' => $currencyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
