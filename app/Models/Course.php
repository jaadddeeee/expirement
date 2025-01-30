<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    protected $connection;

    protected $table = "course";
    protected $cast = ['id' => 'string'];
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','course_title', 'accro'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
