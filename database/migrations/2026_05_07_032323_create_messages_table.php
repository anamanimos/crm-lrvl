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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('wa_message_id', 100)->nullable();
            $table->bigInteger('wa_timestamp')->nullable();
            $table->string('reply_message_id', 100)->nullable();
            $table->text('reply_content')->nullable();
            $table->string('reply_sender_name')->nullable();
            $table->string('type', 20)->default('text');

            $table->enum('direction', ['in', 'out'])->default('in');
            $table->enum('sender_type', ['customer', 'admin', 'system'])->default('customer');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_external_reply')->default(false);
            $table->text('content')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_local_path')->nullable();
            $table->longText('media_meta')->nullable();
            $table->string('media_status', 20)->nullable();
            $table->unsignedTinyInteger('media_attempts')->default(0);
            $table->text('media_last_error')->nullable();
            $table->dateTime('media_started_at')->nullable();
            $table->dateTime('media_uploaded_at')->nullable();
            $table->longText('media_log')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_edited')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
