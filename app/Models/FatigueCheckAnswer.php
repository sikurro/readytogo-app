<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FatigueCheckAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'fatigue_check_id',
        'fatigue_question_id',
        'answer',
    ];

    protected $casts = [
        'answer' => 'boolean',
    ];

    public function fatigueCheck()
    {
        return $this->belongsTo(FatigueCheck::class);
    }

    public function question()
    {
        return $this->belongsTo(FatigueQuestion::class, 'fatigue_question_id');
    }
}
