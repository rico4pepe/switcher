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
    Schema::create('client_routing_configs', function (Blueprint $table) {

    $table->id();

    $table->foreignId('client_id')
        ->constrained('clients')
        ->cascadeOnDelete();

    $table->string('product_type');

    $table->string('network');

    $table->foreignId('primary_vendor_id')
        ->constrained('vendors');

    $table->foreignId('fallback_vendor_id')
        ->nullable()
        ->constrained('vendors');

    $table->boolean('is_active')
        ->default(true);

    $table->timestamps();

    $table->unique([
        'client_id',
        'product_type',
        'network'
    ], 'client_route_unique');
});
}

public function down(): void
{
    Schema::dropIfExists('client_routing_configs');
}
};
