<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $connection;

    protected $table = "students";
    
    protected $primaryKey = 'StudentNo'; // Set the actual primary key
    public $incrementing = false; // If 'StudentNo' is not an auto-incrementing integer
    protected $keyType = 'string'; // Change to 'int' if it's an integer


    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['StudentNo', 'LastName', 'FirstName','MiddleName','Disability','ContactNo','email', 'Picture'
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
