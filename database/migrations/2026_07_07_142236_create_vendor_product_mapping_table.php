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
        Schema::create('vendor_product_mappings', function (Blueprint $table) {
            $table->id();

            // Vendor
            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            // Switcher Product
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Vendor's product identifier
            $table->string('vendor_product_code');

            $table->unsignedInteger('priority')->default(1);

            // Vendor's display name (optional)
            $table->string('vendor_product_name')->nullable();

            // Extra vendor-specific attributes
            $table->json('vendor_metadata')->nullable();

            // Active / Inactive mapping
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate mappings
            $table->unique([
                'vendor_id',
                'product_id'
            ]);

            // Fast lookups
            $table->index('vendor_product_code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_product_mappings');
    }
};
