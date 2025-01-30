<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MaxLimit extends Model
{

    protected $connection;

    protected $table = "maxunits";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','CourseID', 'StudentYear', 'Semester', 'AllowedUnits'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }




}
