<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountBalances extends Model
{

    //wala pa ni na edit para chartaccount
    use HasFactory, SoftDeletes;
    protected $connection;
    protected $table = 'accountbalances';
    protected $fillable = [
        'SchoolYear','Semester','AccountID','AccountDescription','Balance','FundSource','StudentNo','Date '
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }


}


?>
