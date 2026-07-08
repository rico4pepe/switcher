<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Switcher's unique product identifier
           $table->string('product_code')->unique();

            // airtime, data, tv, electricity, betting
            $table->string('product_type');

            // MTN, AIRTEL, GLO, 9MOBILE (nullable for TV, Electricity, Betting)
            $table->string('network')->nullable();

            // Human-readable product name
            $table->string('display_name');

            // Optional description
            $table->text('description')->nullable();

            // Selling amount
           $table->decimal('amount', 12, 2);

            // Validity in days (where applicable)
            $table->unsignedInteger('validity')->nullable();

            // SME, Corporate, Gifting, Broadband, etc.
            $table->string('category')->nullable();

            // Active/Inactive
            $table->boolean('is_active')->default(true);

            // Additional product attributes
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('product_type');
            $table->index('network');
            $table->index(['product_type', 'network']);


                    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
