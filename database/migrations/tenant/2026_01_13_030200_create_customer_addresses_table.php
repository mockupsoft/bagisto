<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('company_name')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('vat_id')->nullable();
            $table->boolean('use_for_shipping')->default(false);
            $table->boolean('default_address')->default(false);
            $table->string('address_type')->default('customer');
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->index('address_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
