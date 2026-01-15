<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('password')->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->boolean('subscribed_to_news_letter')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->tinyInteger('is_suspended')->unsigned()->default(0);
            $table->integer('channel_id')->unsigned()->nullable();
            $table->string('token')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_group_id');
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->nullOnDelete();
            $table->index('channel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
