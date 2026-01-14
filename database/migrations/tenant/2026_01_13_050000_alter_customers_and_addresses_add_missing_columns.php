<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $hasImage = Schema::hasColumn('customers', 'image');
        $hasStatus = Schema::hasColumn('customers', 'status');
        $hasPassword = Schema::hasColumn('customers', 'password');
        $hasSuspended = Schema::hasColumn('customers', 'is_suspended');
        $hasChannelId = Schema::hasColumn('customers', 'channel_id');
        $hasRemember = Schema::hasColumn('customers', 'remember_token');

        $addrHasUseForShipping = Schema::hasColumn('customer_addresses', 'use_for_shipping');
        $addrHasAddressType = Schema::hasColumn('customer_addresses', 'address_type');

        Schema::table('customers', function (Blueprint $table) use (
            $hasImage,
            $hasStatus,
            $hasPassword,
            $hasSuspended,
            $hasChannelId,
            $hasRemember
        ) {
            if (! $hasImage) {
                $table->string('image')->nullable()->after('phone');
            }

            if (! $hasStatus) {
                $table->tinyInteger('status')->default(1)->after('image');
            }

            if (! $hasPassword) {
                $table->string('password')->nullable()->after('status');
            }

            if (! $hasSuspended) {
                $table->tinyInteger('is_suspended')->unsigned()->default(0)->after('is_verified');
            }

            if (! $hasChannelId) {
                $table->integer('channel_id')->unsigned()->nullable()->after('customer_group_id');
                $table->index('channel_id');
            }

            if (! $hasRemember) {
                $table->rememberToken();
            }
        });

        Schema::table('customer_addresses', function (Blueprint $table) use ($addrHasUseForShipping, $addrHasAddressType) {
            if (! $addrHasUseForShipping) {
                $table->boolean('use_for_shipping')->default(false)->after('vat_id');
            }

            if (! $addrHasAddressType) {
                $table->string('address_type')->default('customer')->after('default_address');
                $table->index('address_type');
            }
        });
    }

    public function down(): void
    {
        // Intentionally no-op.
        // Tenant customer schemas may differ across environments; we avoid destructive rollbacks.
        return;
    }
};
