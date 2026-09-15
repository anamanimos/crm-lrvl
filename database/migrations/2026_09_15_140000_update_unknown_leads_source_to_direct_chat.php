<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('customers')
            ->where('source', 'Unknown')
            ->orWhere('source', 'unknown')
            ->update(['source' => 'Direct Chat – Belum Ditanya']);

        DB::table('chat_source_rules')
            ->where('source_name', 'Unknown')
            ->orWhere('source_name', 'unknown')
            ->update(['source_name' => 'Direct Chat – Belum Ditanya']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('customers')
            ->where('source', 'Direct Chat – Belum Ditanya')
            ->update(['source' => 'Unknown']);

        DB::table('chat_source_rules')
            ->where('source_name', 'Direct Chat – Belum Ditanya')
            ->update(['source_name' => 'Unknown']);
    }
};
