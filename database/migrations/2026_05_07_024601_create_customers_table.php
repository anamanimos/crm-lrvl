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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('wa_number', 20)->unique();
            $table->string('lid', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->text('avatar')->nullable();
            $table->dateTime('avatar_last_updated')->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('last_chat_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
