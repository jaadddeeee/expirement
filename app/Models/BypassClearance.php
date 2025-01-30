<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BypassClearance extends Model
{

    protected $connection;
    protected $table = "clearance_bypass";


    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
