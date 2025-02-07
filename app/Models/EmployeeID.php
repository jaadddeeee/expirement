<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeID extends Model
{ 
    
    protected $connection = 'hrmis';

    protected $table = 'employee';

    public $timestamps = false;

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
   
}
