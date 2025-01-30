<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Schedule2 extends Model
{

    protected $connection;

    protected $table = "schedule_time";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','Diy', 'Room', 'DateBegin','DateEnd','tym'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
