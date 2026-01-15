<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            if (! Schema::hasColumn('domains', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('verification_started_at');
            }

            if (! Schema::hasColumn('domains', 'last_failure_reason')) {
                $table->string('last_failure_reason')->nullable()->after('last_checked_at');
            }

            if (! Schema::hasColumn('domains', 'verification_value')) {
                $table->string('verification_value')->nullable()->after('verification_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            if (Schema::hasColumn('domains', 'verification_value')) {
                $table->dropColumn('verification_value');
            }

            if (Schema::hasColumn('domains', 'last_failure_reason')) {
                $table->dropColumn('last_failure_reason');
            }

            if (Schema::hasColumn('domains', 'last_checked_at')) {
                $table->dropColumn('last_checked_at');
            }
        });
    }
};
