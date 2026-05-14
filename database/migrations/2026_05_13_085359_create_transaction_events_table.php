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
            Schema::create('transaction_events', function (Blueprint $table) {

    $table->id();

    $table->foreignId('transaction_id')
        ->constrained()
        ->cascadeOnDelete();

    /*
    |--------------------------------------------------------------------------
    | Event Type
    |--------------------------------------------------------------------------
    */

    $table->string('event_type');

    /*
    |--------------------------------------------------------------------------
    | Human Readable Message
    |--------------------------------------------------------------------------
    */

    $table->text('message')->nullable();

    /*
    |--------------------------------------------------------------------------
    | Structured Event Metadata
    |--------------------------------------------------------------------------
    */

    $table->json('meta')->nullable();

    /*
    |--------------------------------------------------------------------------
    | Event Source
    |--------------------------------------------------------------------------
    */

    $table->string('source')->nullable();

    $table->timestamps();

    $table->index('event_type');
    $table->index('created_at');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_events');
    }
};
