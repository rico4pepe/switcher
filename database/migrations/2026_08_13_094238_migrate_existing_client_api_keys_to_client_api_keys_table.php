<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $clients = DB::table('clients')
            ->whereNotNull('api_key')
            ->get([
                'id',
                'api_key',
            ]);

        foreach ($clients as $client) {
            DB::table('client_api_keys')->insert([
                'client_id' => $client->id,
                'key' => $client->api_key,
                'environment' => 'test',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('client_api_keys')
            ->where('environment', 'test')
            ->delete();
    }
};
