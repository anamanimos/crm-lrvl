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
        Schema::create('chat_source_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('keyword');
            $table->string('source_name');
            $table->string('match_type')->default('contains'); // contains, exact, starts_with
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_source_rules');
    }
};
