<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    protected $connection;

    protected $table = "accountrole";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','EmpID', 'Role', 'StartActive', 'EndActive', 'ClearanceRole', 'DepartmentID'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
