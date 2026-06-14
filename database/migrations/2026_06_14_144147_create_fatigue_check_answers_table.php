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
        Schema::create('fatigue_check_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatigue_check_id')->constrained('fatigue_checks')->onDelete('cascade');
            $table->foreignId('fatigue_question_id')->constrained('fatigue_questions')->onDelete('cascade');
            $table->boolean('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fatigue_check_answers');
    }
};
