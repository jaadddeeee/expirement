<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{

    protected $connection;

    protected $table = "scholar";
    protected $cast = ['id' => 'string'];
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','scholar_name','amount','typ'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
