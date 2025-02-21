<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Scholar extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = 'sch_scholars';

    public $timestamps = false;

    protected $dates = ['deleted_at'];


    protected $fillable = [
        'scholarship_id',
        'student_no',
        'date_awarded',
        'SchoolYear',
        'Semester',
        'bank_account',
        'deleted_at',
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function scholarship()
    {
        return $this->belongsTo(ScholarshipNew::class, 'scholarship_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_no', 'StudentNo');
    }
}
