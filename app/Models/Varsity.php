<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Varsity extends Model
{
    use HasFactory;

    protected $connection;

    protected $table = "var_event";
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'event',
        'status',
        'date'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
