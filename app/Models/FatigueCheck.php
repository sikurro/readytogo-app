<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FatigueCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'questionnaire_status',
        'reaction_time_ms',
        'is_fit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
