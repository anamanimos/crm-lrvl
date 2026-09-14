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
        // Disassociate any group messages from customer records to prevent group chats leaking into 1-on-1 customer chats
        DB::table('messages')
            ->whereNotNull('wa_group_id')
            ->whereNotNull('customer_id')
            ->update(['customer_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data separation
    }
};
