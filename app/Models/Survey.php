<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $connection = "evaluation";
    protected $fillable = [
        'id','title','description','date_start','date_end'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
