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
    Schema::table('clients', function (Blueprint $table) {

        $table->string('organization_name')
            -> nullable()
            ->after('name');

        $table->string('email')
            ->nullable()
            ->after('organization_name');

        $table->string('phone')
            ->nullable()
            ->after('email');

        $table->string('contact_person')
            ->nullable()
            ->after('phone');

        $table->timestamp('last_used_at')
            ->nullable()
            ->after('is_active');
    });
}

public function down(): void
{
    Schema::table('clients', function (Blueprint $table) {

        $table->dropColumn([
            'organization_name',
            'email',
            'phone',
            'contact_person',
            'last_used_at',
        ]);
    });
}
};
