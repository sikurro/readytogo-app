<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FatigueQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'is_active',
        'safe_answer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'safe_answer' => 'boolean',
    ];
}
