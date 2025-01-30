<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CLDORMG extends Model
{

    protected $connection;
    protected $table = "clearance_dormg";
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
