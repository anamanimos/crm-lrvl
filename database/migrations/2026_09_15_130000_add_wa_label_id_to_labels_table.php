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
        Schema::table('labels', function (Blueprint $table) {
            $table->string('wa_label_id', 50)->nullable()->unique()->after('id');
            $table->integer('order_index')->default(0)->after('is_active');
            $table->string('predefined_id', 50)->nullable()->after('order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropColumn(['wa_label_id', 'order_index', 'predefined_id']);
        });
    }
};
