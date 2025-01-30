<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TMPNSTP extends Model
{

    protected $connection;
    protected $table = "tmp_nstp";
    protected $fillable = [
        'id','StudentNo', 'sy1', 'sem1', 'grade1', 'sy2','sem2','grade2','subject1','subject2'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }


}
