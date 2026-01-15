<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSalesSeeder
{
    /**
     * Seed minimal checkout prerequisites for tenant DB.
     */
    public function seed(): void
    {
        $this->seedRuntimeConfig();
        $this->seedTenant();
    }

    /**
     * Ensure at least one shipping + payment method is active.
     *
     * Bagisto uses config merge keys `carriers` and `payment_methods`.
     */
    public function seedRuntimeConfig(): void
    {
        config()->set('carriers.free.active', true);
        config()->set('carriers.free.default_rate', '0');

        config()->set('payment_methods.cashondelivery.active', true);
        config()->set('payment_methods.cashondelivery.title', 'Cash On Delivery');
        config()->set('payment_methods.cashondelivery.description', 'Cash On Delivery');
    }

    /**
     * Seed tenant DB rows needed by checkout.
     */
    public function seedTenant(): void
    {
        $schema = Schema::connection('tenant');

        if (! $schema->hasTable('inventory_sources')) {
            return;
        }

        $conn = DB::connection('tenant');
        $now = now();

        $exists = $conn->table('inventory_sources')->where('code', 'default')->exists();

        if (! $exists) {
            $conn->table('inventory_sources')->insert([
                'code' => 'default',
                'name' => 'Default',
                'description' => 'Default inventory source',
                'contact_name' => 'Store',
                'contact_email' => 'store@example.com',
                'contact_number' => '0000000000',
                'contact_fax' => null,
                'country' => 'TR',
                'state' => 'TR-34',
                'city' => 'Istanbul',
                'street' => 'Street 1',
                'postcode' => '34000',
                'priority' => 0,
                'latitude' => null,
                'longitude' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
