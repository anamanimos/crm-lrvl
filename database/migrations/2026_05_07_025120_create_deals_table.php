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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('deal_stage_id')->constrained();
            $table->decimal('expected_value', 15, 2)->default(0);
            $table->string('source', 100)->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->dateTime('next_followup_date')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->string('lost_reason')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
