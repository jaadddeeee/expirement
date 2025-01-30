<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $connection;

    protected $table = "students";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','StudentNo', 'LastName', 'FirstName','MiddleName','Disability','ContactNo','email'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function course(){
        return $this->hasOne(Course::class, 'id', 'Course');
    }

    public function Major(){
      return $this->hasOne(Major::class, 'id', 'major');
    }

}
