<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FacultyEvaluationSchedule extends Model
{

    protected $connection = "evaluation";
    protected $table = "facultyevaluationschedule";

    protected $fillable = [
        'id','date', 'SchoolYear', 'Semester'
    ];

}
