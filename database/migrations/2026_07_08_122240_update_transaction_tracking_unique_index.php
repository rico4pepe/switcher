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
    Schema::table('transactions', function (Blueprint $table) {

        $table->dropUnique(
            'transactions_tracking_id_unique'
        );

        $table->unique([
            'client_id',
            'tracking_id'
        ]);
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('transactions', function (Blueprint $table) {

        $table->dropUnique([
            'client_id',
            'tracking_id'
        ]);

        $table->unique('tracking_id');
    });
}
};
