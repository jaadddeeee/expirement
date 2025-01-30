<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $connection = "evaluation";
    protected $fillable = [
        'id','question_id', 'answer'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
