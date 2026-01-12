<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique();
            $table->string('database_name');
            $table->string('database_host')->default('127.0.0.1');
            $table->unsignedInteger('database_port')->default(3306);
            $table->string('database_username');
            $table->text('database_password');
            $table->string('database_prefix')->nullable();
            $table->string('status')->default('provisioning'); // provisioning|ready|failed
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_databases');
    }
};
