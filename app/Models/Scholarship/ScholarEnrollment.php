<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ScholarEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = 'sch_scholar_enrollments';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'scholar_id',
        'school_year',
        'semester'
    ];

    public function __construct()
    {
        $this->connection = strtolower(session('campus'));
    }

    public function scholar()
    {
        return $this->belongsTo(ScholarDetail::class, 'scholar_id', 'id');
    }
}
