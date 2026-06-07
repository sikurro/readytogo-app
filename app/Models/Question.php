<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['question_text', 'question_image', 'timer_seconds', 'risk_level', 'reference'];

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
