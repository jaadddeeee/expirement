<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{

    protected $connection;

    protected $table = "purposes";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','Description'
    ];

    public function __construct(){
        $this->connection = 'sg';
    }
}
