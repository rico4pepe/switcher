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
       Schema::create('routing_config', function (Blueprint $table) {
                $table->id();

                $table->string('product_type');
                $table->string('network');

                $table->unsignedBigInteger('primary_vendor_id');
                $table->unsignedBigInteger('fallback_vendor_id')->nullable();

                $table->enum('mode', ['manual', 'auto'])->default('manual');

                $table->boolean('auto_failover_enabled')->default(false);

                $table->integer('failover_threshold_pct')->default(50);
                $table->integer('failover_window_mins')->default(5);
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->integer('min_sample_size')->default(10);

                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_connfigs');
    }
};
