<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_routing_configs', function (Blueprint $table) {

            $table->index(
                [
                    'client_id',
                    'product_type',
                    'network',
                    'is_active',
                ],
                'client_route_lookup_index'
            );

        });
    }

    public function down(): void
    {
        Schema::table('client_routing_configs', function (Blueprint $table) {

            $table->dropIndex('client_route_lookup_index');

        });
    }
};
