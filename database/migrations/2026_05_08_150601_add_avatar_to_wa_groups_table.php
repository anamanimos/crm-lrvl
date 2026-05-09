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
        Schema::table('wa_groups', function (Blueprint $table) {
            $table->text('avatar')->nullable()->after('name');
            $table->dateTime('avatar_last_updated')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_groups', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'avatar_last_updated']);
        });
    }
};
