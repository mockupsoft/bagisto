<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'parent_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                $table->index('parent_id');
            });
        }

        if (Schema::hasTable('product_flat') && ! Schema::hasColumn('product_flat', 'parent_id')) {
            Schema::table('product_flat', function (Blueprint $table) {
                $table->unsignedInteger('parent_id')->nullable()->after('id');
                $table->index('parent_id');
            });
        }
    }

    public function down(): void
    {
        // No-op: avoid destructive schema changes in tenant DB rollbacks.
        return;
    }
};
