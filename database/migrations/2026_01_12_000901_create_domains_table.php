<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('type')->default('subdomain'); // subdomain|custom
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
