<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_attempts indexes
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index('month_year', 'idx_qa_month_year');
            $table->index(['user_id', 'quiz_id', 'created_at'], 'idx_qa_user_quiz_date');
        });

        // fatigue_checks indexes
        Schema::table('fatigue_checks', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_fc_user_date');
        });

        // incidents indexes
        Schema::table('incidents', function (Blueprint $table) {
            $table->index('status', 'idx_incidents_status');
            $table->index('category', 'idx_incidents_category');
            $table->index('severity', 'idx_incidents_severity');
            $table->index(['status', 'severity'], 'idx_incidents_status_severity');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_qa_month_year');
            $table->dropIndex('idx_qa_user_quiz_date');
        });

        Schema::table('fatigue_checks', function (Blueprint $table) {
            $table->dropIndex('idx_fc_user_date');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_status');
            $table->dropIndex('idx_incidents_category');
            $table->dropIndex('idx_incidents_severity');
            $table->dropIndex('idx_incidents_status_severity');
        });
    }
};
