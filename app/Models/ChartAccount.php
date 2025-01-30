<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartAccount extends Model
{

    //wala pa ni na edit para chartaccount
    use HasFactory, SoftDeletes;
    protected $connection;
    protected $table = 'credited';
    protected $with = ['Prospectos','Student'];
    protected $fillable = [
        'StudentNo','courseno','encoder','grade','DateCredited','IPAddress','Description','FileName'
    ];

    public function Prospectos()
    {
        return $this->belongsTo(Prospectos::class,'courseno','id');
    }

    public function Student()
    {
        return $this->belongsTo(Student::class,'StudentNo','StudentNo');
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    protected $dates = ['deleted_at'];

}


?>
