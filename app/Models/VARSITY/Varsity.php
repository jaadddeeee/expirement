<?php

namespace App\Models\VARSITY;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;


class Varsity extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = "var_varsity";
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'StudentNo',
        'VarsityEvent',
        'SchoolYear',
        'Semester',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'VarsityEvent','id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentNo', 'StudentNo');
    }

    public function listVarsity()
    {
        return $this->hasMany(ListVarsity::class, 'StudentNo', 'StudentNo');
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
