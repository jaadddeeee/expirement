<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{

    protected $connection;

    protected $table = "registration";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','RegistrationID', 'StudentNo', 'SchoolLevel', 'SchoolYear', 'Semester', 'StudentYear', 'Section',
        'Course','Major','finalize'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function course(){
      return $this->hasOne(Course::class, 'id', 'Course');
    }

    public function major(){
      return $this->hasOne(Major::class, 'id', 'Major');
    }

    public function subjects(){
      return $this->hasMany(Enrolled::class, 'gradesid', 'RegistrationID');
    }
}
