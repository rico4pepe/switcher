<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_routing_configs', function (Blueprint $table) {

            $table->enum('mode', ['manual', 'auto'])
                ->default('manual')
                ->after('fallback_vendor_id');

            $table->boolean('auto_failover_enabled')
                ->default(false)
                ->after('mode');

            $table->integer('failover_threshold_pct')
                ->default(50)
                ->after('auto_failover_enabled');

            $table->integer('failover_window_mins')
                ->default(5)
                ->after('failover_threshold_pct');

            $table->integer('min_sample_size')
                ->default(10)
                ->after('failover_window_mins');
        });
    }

    public function down(): void
    {
        Schema::table('client_routing_configs', function (Blueprint $table) {

            $table->dropColumn([
                'mode',
                'auto_failover_enabled',
                'failover_threshold_pct',
                'failover_window_mins',
                'min_sample_size',
            ]);
        });
    }
};
