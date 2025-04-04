<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;


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
        'contact_no'
    ];

    public function __construct()
    {
        $this->connection = strtolower(session('campus'));
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_no', 'StudentNo');
    }
}
