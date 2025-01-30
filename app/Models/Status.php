<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $connection;

    protected $table = "status";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','status'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
