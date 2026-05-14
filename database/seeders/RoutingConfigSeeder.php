<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoutingConfig;

class RoutingConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // ── Airtime ──
            ['product_type' => 'airtime', 'network' => 'MTN'],
            ['product_type' => 'airtime', 'network' => 'GLO'],
            ['product_type' => 'airtime', 'network' => 'AIRTEL'],
            ['product_type' => 'airtime', 'network' => '9MOBILE'],

            // ── Data ──
            ['product_type' => 'data', 'network' => 'MTN'],
            ['product_type' => 'data', 'network' => 'GLO'],
            ['product_type' => 'data', 'network' => 'AIRTEL'],
            ['product_type' => 'data', 'network' => '9MOBILE'],

            // ── TV ──
            ['product_type' => 'tv', 'network' => 'DSTV'],
            ['product_type' => 'tv', 'network' => 'GOTV'],
            ['product_type' => 'tv', 'network' => 'STARTIMES'],

            // ── Electricity ──
            ['product_type' => 'electricity', 'network' => 'PREPAID'],
            ['product_type' => 'electricity', 'network' => 'POSTPAID'],

            // ── Internet ──
            ['product_type' => 'internet', 'network' => 'SMILE'],
            ['product_type' => 'internet', 'network' => 'SPECTRANET'],

            // ── Betting ──
            ['product_type' => 'betting', 'network' => 'BET'],

            // ── Education ──
            ['product_type' => 'education', 'network' => 'WAEC'],

            // ── Streaming ──
            ['product_type' => 'streaming', 'network' => 'SHOWMAX'],
        ];

        foreach ($configs as $config) {
            RoutingConfig::updateOrCreate(
                [
                    'product_type' => $config['product_type'],
                    'network'      => $config['network'],
                ],
                [
                    'primary_vendor_id'     => 1, // Oatek
                    'fallback_vendor_id'    => null,
                    'mode'                  => 'manual',
                    'auto_failover_enabled' => false,
                    'failover_threshold_pct'=> 50,
                    'failover_window_mins'  => 5,
                    'min_sample_size'       => 10,
                    'is_active'             => true,
                ]
            );
        }
    }
}
