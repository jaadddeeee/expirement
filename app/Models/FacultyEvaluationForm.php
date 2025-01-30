<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FacultyEvaluationForm extends Model
{

    protected $connection = "evaluation";
    protected $table = "facultyevaluationform";

    protected $fillable = [
        'id','Question', 'Sequence', 'GroupID'
    ];

}
