<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'theme', 'topic_id', 'duration_minutes', 'is_active', 'is_daily_quiz', 'daily_question_limit'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
