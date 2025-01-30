<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $connection;

    protected $table = "employees";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','FirstName', 'MiddleName', 'LastName', 'Department' ,'EmploymentStatus','Cellphone',
        'AgencyNumber','CurrentItem','EmailAddress','profilephoto'

    ];

    public function department(){

      return $this->hasOne(Department::class, 'id', 'Department');
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
