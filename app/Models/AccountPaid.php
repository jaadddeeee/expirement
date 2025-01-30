<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountPaid extends Model
{

    //wala pa ni na edit para chartaccount
    use HasFactory, SoftDeletes;
    protected $connection;
    protected $table = 'accountpaid';
    protected $fillable = [
        'SchoolYear','Semester','StudentNo','Amount','withMatched'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }


}


?>
