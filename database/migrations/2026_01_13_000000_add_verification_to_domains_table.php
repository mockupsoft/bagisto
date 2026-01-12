<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('verification_token')->nullable()->index();
            $table->string('verification_method')->nullable();
            $table->timestamp('verification_started_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['verification_token']);
            $table->dropColumn(['verification_token', 'verification_method', 'verification_started_at']);
        });
    }
};
