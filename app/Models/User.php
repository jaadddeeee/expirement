<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    protected $connection;

    protected $table = "accountsuser";

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'Emp_No', 'UserName', 'Password', 'Active','AccountLevel','AllowSuper','HRMISID'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'Password',
    ];

    public function emp(){
        return $this->hasOne(Employee::class, 'id', 'Emp_No');
    }

    public function role(){
      return $this->hasMany(Role::class, 'EmpID', 'Emp_No');
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
