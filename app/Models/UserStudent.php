<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserStudent extends Model
{

    protected $connection;

    protected $table = "accountstudents";


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'StudentNo', 'Password', 'DateCreated', 'WayCreation','CreatedBy','isActive','ExtendRequest','forclearance','ClearanceFlag','Department'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    // public function student(){
    //     return $this->hasOne(Student::class, 'StudentNo', 'StudentNo');
    // }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
