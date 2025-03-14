<?php

namespace App\Models\Varsity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;

class ListVarsity extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;
    protected $table = "var_scuaa_list";
    protected $fillable = [
        'id','StudentNo', 'SchoolYear','Event'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function varsity()
    {
        return $this->belongsTo(Varsity::class, 'StudentNo', 'StudentNo');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentNo', 'StudentNo');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'Event', 'id');
    }

}
