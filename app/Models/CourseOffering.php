<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{

    protected $connection;

    protected $table;
    protected $casts = ['id' => 'string'];
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function __construct(){
        $this->connection = strtolower(session('campus'));
        $this->table = "courseoffering".(session('schoolyear')).(session('semester'));
    }

    protected $fillable = [
        'id','course', 'student_year', 'semester','major','sched','teacher','max_limit',
        'school_year','section','sort_order','courseid','coursecode','sched2','avail',
        'LockExtend','ChangeExtended','Scheme','fee','proceed','AddedBy'
    ];

    public function enrolled(){
      return $this->hasMany(Enrolled::class, 'courseofferingid', 'id');
    }

    public function schedule(){
      return $this->hasOne(Schedule::class, 'id', 'sched');
    }

    public function schedule2(){
      return $this->hasOne(Schedule::class, 'id', 'sched2');
    }

    public function employee(){
      return $this->hasOne(Employee::class, 'id', 'teacher');
    }

    public function subject(){
      return $this->hasOne(Subject::class, 'id', 'courseid');
    }

}
