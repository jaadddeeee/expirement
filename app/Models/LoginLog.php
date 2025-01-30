<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{

    protected $connection;
    protected $table = "userlog";

    protected $fillable = [
        'id','UserName', 'IPAddress', 'Emp_No', 'Platform', 'UserID', 'SSO', 'Remarks' ,'Browser', 'Device'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
