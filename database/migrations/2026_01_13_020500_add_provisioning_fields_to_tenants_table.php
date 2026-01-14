<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('tenants', 'store_name')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('store_name')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('tenants', 'subdomain')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('subdomain')->nullable()->after('slug');
                $table->unique('subdomain', 'tenants_subdomain_unique');
            });
        }

        if (! Schema::hasColumn('tenants', 'provisioning_started_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->timestamp('provisioning_started_at')->nullable()->after('plan');
            });
        }

        if (! Schema::hasColumn('tenants', 'provisioning_finished_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->timestamp('provisioning_finished_at')->nullable()->after('provisioning_started_at');
            });
        }

        if (! Schema::hasColumn('tenants', 'last_error')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->text('last_error')->nullable()->after('provisioning_finished_at');
            });
        }

        if (! Schema::hasColumn('tenants', 'onboarding_completed_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('last_error');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'store_name')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('store_name');
            });
        }

        if (Schema::hasColumn('tenants', 'subdomain')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropUnique('tenants_subdomain_unique');
                $table->dropColumn('subdomain');
            });
        }

        if (Schema::hasColumn('tenants', 'provisioning_started_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('provisioning_started_at');
            });
        }

        if (Schema::hasColumn('tenants', 'provisioning_finished_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('provisioning_finished_at');
            });
        }

        if (Schema::hasColumn('tenants', 'last_error')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('last_error');
            });
        }

        if (Schema::hasColumn('tenants', 'onboarding_completed_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('onboarding_completed_at');
            });
        }
    }
};
