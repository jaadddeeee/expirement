<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DayList extends Model
{

    protected $connection;

    protected $table = "listday";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','DayOfWeek', 'FullName'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
