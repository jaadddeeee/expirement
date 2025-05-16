<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipApplication extends Model
{
    use HasFactory;

    protected $table = "sch_applications";

    protected $fillable = [
        'scholarship_id',
        'slots',
        'status',
        'start_date',
        'deadline_date',
        'eligible_courses',
        'eligible_majors',
        'eligible_yearLevel',
        'school_year',
        'semester'
    ];

    protected $casts = [
        'eligible_courses' => 'array',
        'eligible_majors' => 'array',
        'eligible_yearLevel' => 'array',
    ];

    public function __construct()
    {
        $this->connection = strtolower(session('campus'));
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
