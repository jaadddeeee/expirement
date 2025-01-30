<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{

    protected $connection;

    protected $table = "transcript";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id','courseno', 'coursetitle', 'units','lab','lec'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
