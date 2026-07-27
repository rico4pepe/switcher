<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routing_configs', function (Blueprint $table) {

            // Prevent duplicate routing policies
            $table->unique(
                ['product_type', 'network'],
                'routing_config_product_network_unique'
            );

            // Optimise routing lookups
            $table->index(
                ['product_type', 'network', 'is_active'],
                'routing_config_lookup_index'
            );

            // Enforce referential integrity
            $table->foreign('primary_vendor_id')
                ->references('id')
                ->on('vendors')
                ->restrictOnDelete();

            $table->foreign('fallback_vendor_id')
                ->references('id')
                ->on('vendors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('routing_configs', function (Blueprint $table) {

            $table->dropForeign(['primary_vendor_id']);
            $table->dropForeign(['fallback_vendor_id']);

            $table->dropUnique(
                'routing_config_product_network_unique'
            );

            $table->dropIndex(
                'routing_config_lookup_index'
            );
        });
    }
};
