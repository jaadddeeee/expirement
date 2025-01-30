<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CLOSAS extends Model
{

    protected $connection;
    protected $table = "clearance_osas";
    protected $fillable = [
        'id','StudentNo', 'SchoolYear', 'Semester', 'Description', 'AddedBy'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function student(){
      return $this->hasOne(Student::class, 'StudentNo', 'StudentNo');
    }

}
