<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DevBagistoSeeder - Minimum required data for Bagisto admin panel to work.
 *
 * This seeder is idempotent: running it multiple times will not duplicate rows.
 * It inserts the bare minimum records needed for:
 * - php artisan route:list (requires channel)
 * - Admin panel login and navigation
 * - MockupSoft/Companies module to function
 *
 * SECURITY:
 * - Only runs in local/testing environments OR when DEV_SEEDER_ENABLED=true
 * - Admin credentials read from environment variables
 *
 * Environment Variables:
 * - DEV_SEEDER_ENABLED: Set to "true" to allow seeding in non-local environments
 * - DEV_ADMIN_EMAIL: Admin email (default: admin@example.com)
 * - DEV_ADMIN_PASSWORD: Admin password (default: admin123, warns if using default)
 *
 * Tables touched:
 * - locales: Required for channel default_locale_id FK
 * - currencies: Required for channel base_currency_id FK
 * - categories: Required for channel root_category_id FK
 * - category_translations: Required for category name display
 * - channels: Required for Core::getCurrentChannelCode() - prevents null errors
 * - channel_translations: Required for channel name display
 * - customer_groups: Required for some admin functionality
 * - roles: Required for admin user role_id FK
 * - admins: Required for admin panel login
 *
 * Usage:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=DevBagistoSeeder
 *
 * With custom credentials:
 *   DEV_ADMIN_EMAIL=myemail@example.com DEV_ADMIN_PASSWORD=mysecret php artisan db:seed --class=DevBagistoSeeder
 */
class DevBagistoSeeder extends Seeder
{
    /**
     * Admin email from environment.
     */
    protected string $adminEmail;

    /**
     * Admin password from environment.
     */
    protected string $adminPassword;

    /**
     * Whether using default password (triggers warning).
     */
    protected bool $usingDefaultPassword = false;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->guardEnvironment();
        $this->loadCredentials();

        $this->seedLocales();
        $this->seedCurrencies();
        $this->seedCategories();
        $this->seedChannels();
        $this->seedCustomerGroups();
        $this->seedRoles();
        $this->seedAdmins();

        $this->command->info('');
        $this->command->info('✅ DevBagistoSeeder completed successfully.');
        $this->command->info("   Admin login: {$this->adminEmail}");

        if ($this->usingDefaultPassword) {
            $this->command->warn('   ⚠️  Using default password! Set DEV_ADMIN_PASSWORD in .env for security.');
        }
    }

    /**
     * Guard: Only allow seeding in safe environments.
     *
     * @throws \RuntimeException
     */
    protected function guardEnvironment(): void
    {
        $allowedEnvironments = ['local', 'testing'];
        $currentEnv = App::environment();
        $seederEnabled = filter_var(env('DEV_SEEDER_ENABLED', false), FILTER_VALIDATE_BOOLEAN);

        if (in_array($currentEnv, $allowedEnvironments)) {
            $this->command->info("Environment: {$currentEnv} (allowed)");

            return;
        }

        if ($seederEnabled) {
            $this->command->warn("Environment: {$currentEnv} (DEV_SEEDER_ENABLED=true, proceeding with caution)");

            return;
        }

        $this->command->error("❌ DevBagistoSeeder blocked: environment '{$currentEnv}' is not allowed.");
        $this->command->error('   Set DEV_SEEDER_ENABLED=true in .env to override (not recommended for production).');

        throw new \RuntimeException("DevBagistoSeeder cannot run in '{$currentEnv}' environment.");
    }

    /**
     * Load admin credentials from environment.
     */
    protected function loadCredentials(): void
    {
        $this->adminEmail = env('DEV_ADMIN_EMAIL', 'admin@example.com');
        $this->adminPassword = env('DEV_ADMIN_PASSWORD', 'admin123');

        if ($this->adminPassword === 'admin123') {
            $this->usingDefaultPassword = true;
        }
    }

    /**
     * Seed locales table.
     */
    protected function seedLocales(): void
    {
        DB::table('locales')->updateOrInsert(
            ['code' => 'en'],
            [
                'name'       => 'English',
                'direction'  => 'ltr',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✓ Locales seeded');
    }

    /**
     * Seed currencies table.
     */
    protected function seedCurrencies(): void
    {
        DB::table('currencies')->updateOrInsert(
            ['code' => 'USD'],
            [
                'name'       => 'US Dollar',
                'symbol'     => '$',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✓ Currencies seeded');
    }

    /**
     * Seed categories table.
     */
    protected function seedCategories(): void
    {
        DB::table('categories')->updateOrInsert(
            ['id' => 1],
            [
                'position'   => 1,
                'status'     => 1,
                '_lft'       => 1,
                '_rgt'       => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $category = DB::table('categories')->where('id', 1)->first();

        if ($category) {
            $localeId = DB::table('locales')->where('code', 'en')->value('id');

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                [
                    'name'        => 'Root',
                    'slug'        => 'root',
                    'description' => 'Root Category',
                    'locale_id'   => $localeId,
                ]
            );
        }

        $this->command->info('✓ Categories seeded');
    }

    /**
     * Seed channels table.
     */
    protected function seedChannels(): void
    {
        $localeId = DB::table('locales')->where('code', 'en')->value('id');
        $currencyId = DB::table('currencies')->where('code', 'USD')->value('id');
        $categoryId = DB::table('categories')->first()->id ?? 1;

        DB::table('channels')->updateOrInsert(
            ['code' => 'default'],
            [
                'theme'             => 'default',
                'root_category_id'  => $categoryId,
                'default_locale_id' => $localeId,
                'base_currency_id'  => $currencyId,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $channelId = DB::table('channels')->where('code', 'default')->value('id');

        // Channel translation
        DB::table('channel_translations')->updateOrInsert(
            ['channel_id' => $channelId, 'locale' => 'en'],
            [
                'name'        => 'Default',
                'description' => 'Default channel',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // Link channel to locale (pivot) - idempotent check
        if (! DB::table('channel_locales')->where('channel_id', $channelId)->where('locale_id', $localeId)->exists()) {
            DB::table('channel_locales')->insert([
                'channel_id' => $channelId,
                'locale_id'  => $localeId,
            ]);
        }

        // Link channel to currency (pivot) - idempotent check
        if (! DB::table('channel_currencies')->where('channel_id', $channelId)->where('currency_id', $currencyId)->exists()) {
            DB::table('channel_currencies')->insert([
                'channel_id'  => $channelId,
                'currency_id' => $currencyId,
            ]);
        }

        $this->command->info('✓ Channels seeded');
    }

    /**
     * Seed customer_groups table.
     */
    protected function seedCustomerGroups(): void
    {
        DB::table('customer_groups')->updateOrInsert(
            ['code' => 'general'],
            [
                'name'            => 'General',
                'is_user_defined' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        DB::table('customer_groups')->updateOrInsert(
            ['code' => 'guest'],
            [
                'name'            => 'Guest',
                'is_user_defined' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        $this->command->info('✓ Customer groups seeded');
    }

    /**
     * Seed roles table.
     */
    protected function seedRoles(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 1],
            [
                'name'            => 'Administrator',
                'description'     => 'Full access administrator role',
                'permission_type' => 'all',
                'permissions'     => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        $this->command->info('✓ Roles seeded');
    }

    /**
     * Seed admins table.
     */
    protected function seedAdmins(): void
    {
        DB::table('admins')->updateOrInsert(
            ['email' => $this->adminEmail],
            [
                'name'       => 'Admin',
                'password'   => Hash::make($this->adminPassword),
                'status'     => 1,
                'role_id'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✓ Admins seeded');
    }
}
