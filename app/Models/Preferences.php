<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Preferences extends Model
{

    protected $connection;

    protected $table = "defaultvalue";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','DefaultName', 'DefaultValue'
    ];

    public function __construct(){
      $this->connection = strtolower(session('campus'));
  }
}
