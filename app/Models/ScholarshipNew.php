<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ScholarshipNew extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = "sch_scholarships";
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = false;

    protected $fillable = [
        'id', 'sch_name', 'sch_type', 'ext_type'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

    public function scholars()
    {
        return $this->hasMany(Scholar::class, 'scholarship_id');
    }

}
