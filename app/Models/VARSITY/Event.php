<?php

namespace App\Models\VARSITY;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = "var_event";
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'event'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
