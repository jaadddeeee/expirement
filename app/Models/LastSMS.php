<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastSMS extends Model
{
    use HasFactory;

    protected $connection ;
    protected $table = "last_sms_sender";
    protected $fillable = [
        'id','employee_id', 'StudentNo','year','month','schedule_id','Message'
    ];
    public function __construct(){
      $this->connection = strtolower(session('campus'));
  }
}
