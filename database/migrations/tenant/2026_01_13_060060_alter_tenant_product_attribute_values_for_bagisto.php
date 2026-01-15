<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_attribute_values')) {
            return;
        }

        Schema::table('product_attribute_values', function (Blueprint $table) {
            if (! Schema::hasColumn('product_attribute_values', 'boolean_value')) {
                $table->boolean('boolean_value')->nullable()->after('integer_value');
            }

            if (! Schema::hasColumn('product_attribute_values', 'float_value')) {
                $table->decimal('float_value', 12, 4)->nullable()->after('boolean_value');
            }

            // Keep backwards compatibility: older tenant schema used `decimal_value`.
            if (! Schema::hasColumn('product_attribute_values', 'decimal_value')) {
                $table->decimal('decimal_value', 12, 4)->nullable()->after('float_value');
            }
        });
    }

    public function down(): void
    {
        // No-op.
        return;
    }
};
