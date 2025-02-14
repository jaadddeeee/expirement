<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIDPayment extends Model
{
    protected $connection;

    protected $table = "stuid_payment";

    protected $fillable = [
        'id', 'StudentNo', 'or_no', 'date_of_payment', 'Picture'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
