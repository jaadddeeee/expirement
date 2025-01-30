<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FacultyEvaluationResult extends Model
{

    protected $connection = "evaluation";
    protected $table;
    protected $fillable = [
        'id','StudentNo', 'EmployeeID', 'Rating','SchoolYear','Semester','Campus','ScheduleID','QuestionID'
    ];

    public function setT($sy,$sem){
      $this->table = "facultyevaluationresult".$sy.$sem;
    }

}
