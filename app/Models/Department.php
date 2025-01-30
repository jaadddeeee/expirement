<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{

    protected $connection;

    protected $table = "department";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','Description', 'DepartmentHead', 'DepartmentName', 'Designation'
    ];

    public function head(){

      return $this->hasOne(Employee::class, 'id', 'DepartmentHead');
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
