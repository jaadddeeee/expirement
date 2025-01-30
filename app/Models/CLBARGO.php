<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CLBARGO extends Model
{

    protected $connection;
    protected $table = "clearance_bargo";

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
