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
        Schema::table('auto_replies', function (Blueprint $table) {
            $table->json('active_days')->nullable()->after('delay_seconds'); // ['mon', 'tue', ...]
            $table->json('active_times')->nullable()->after('active_days'); // [['start' => '08:00', 'end' => '17:00'], ...]
            $table->string('media_path')->nullable()->after('active_times');
            $table->string('media_type')->nullable()->after('media_path'); // image, document
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_replies', function (Blueprint $table) {
            $table->dropColumn(['active_days', 'active_times', 'media_path', 'media_type']);
        });
    }
};
