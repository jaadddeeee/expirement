<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;

class ScholarDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = 'sch_scholar_details';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'student_no',
        'scholarship_id',
        'date_awarded',
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

    public function enrollments()
    {
        return $this->hasMany(ScholarEnrollment::class, 'scholar_id', 'id');
    }
}
