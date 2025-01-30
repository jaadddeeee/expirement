<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MaxLimitByPass extends Model
{

    protected $connection;

    protected $table = "maxunit_bypass";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','StudentNo', 'SchoolYear', 'Semester', 'Units'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }




}
