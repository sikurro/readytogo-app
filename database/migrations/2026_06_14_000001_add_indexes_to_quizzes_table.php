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
        Schema::table('quizzes', function (Blueprint $table) {
            // Digunakan untuk filter status dan tipe
            $table->index('is_active', 'idx_quizzes_is_active');
            $table->index('is_daily_quiz', 'idx_quizzes_is_daily_quiz');
            // Digunakan untuk default sort
            $table->index('created_at', 'idx_quizzes_created_at');
            // Composite index untuk filter kombinasi umum: status + tipe
            $table->index(['is_active', 'is_daily_quiz'], 'idx_quizzes_active_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('idx_quizzes_is_active');
            $table->dropIndex('idx_quizzes_is_daily_quiz');
            $table->dropIndex('idx_quizzes_created_at');
            $table->dropIndex('idx_quizzes_active_type');
        });
    }
};
