<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_api_keys', function (Blueprint $table) {
            $table->id();

           $table->foreignId('client_id')
    ->constrained()
    ->cascadeOnDelete();

$table->string('key', 64)
    ->unique();

$table->string('environment');

$table->boolean('is_active')
    ->default(true);

$table->timestamps();

$table->unique([
    'client_id',
    'environment',
]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_api_keys');
    }


};
