<?php

namespace App\Models\Scholarship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipRequirements extends Model
{
    use HasFactory;

    protected $table = 'sch_requirements';

    protected $fillable = [
        'scholarship_id',
        'quantity',
        'sch_requirements',
    ];

    public function __construct()
    {
        $this->connection = strtolower(session('campus'));
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id');
    }
}
