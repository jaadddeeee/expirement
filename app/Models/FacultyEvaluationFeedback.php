<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FacultyEvaluationFeedback extends Model
{

    protected $connection = "evaluation";
    protected $table = "facultyevaluationfeedback";

    protected $fillable = [
        'id','StudentID', 'EmployeeID', 'ScheduleID','SchoolYear','Semester','Feedback','Campus'
    ];

}
