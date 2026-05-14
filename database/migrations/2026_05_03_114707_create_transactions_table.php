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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->uuid('ringo_reference')->unique();
            $table->string('tracking_id')->unique();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            $table->string('product_type');
            $table->string('network');

            $table->string('beneficiary')->nullable();

            $table->decimal('amount', 12, 2);

            $table->string('status')->default('pending');
            $table->string('ringo_response_code')->nullable();

            $table->string('vendor_reference')->nullable();

            $table->integer('response_time_ms')->nullable();

            $table->json('raw_vendor_request')->nullable();
            $table->json('raw_vendor_response')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
