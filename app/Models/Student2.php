<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student2 extends Model
{
    protected $connection;

    protected $table = "students2";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','StudentNo', 'BloodType', 'Allergy'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
