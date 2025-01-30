<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CLDORMB extends Model
{

    protected $connection;
    protected $table = "clearance_dormb";
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
