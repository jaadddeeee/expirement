<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Grades extends Model
{

    protected $connection;
    protected $table;
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function __construct(){
        $this->connection = strtolower(session('campus'));
        $this->table = "grades".session('msy').session('msem');
    }


    protected $fillable = [
        'id','gradesid','courseofferingid','midterm','finalterm','inc','final','sched','StudentNo',
        'tym_put','tym_update','encoder_encode','encoder_update'
    ];

    public function EnrolledSubject(){
      return $this->hasOne(Subject::class, 'id', 'sched');
    }

}
