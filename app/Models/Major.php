<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{

    protected $connection;

    protected $table = "major";
    protected $cast = ['id' => 'string'];
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','course_major'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
