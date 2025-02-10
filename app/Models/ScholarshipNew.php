<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ScholarshipNew extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "scholarship_new";
    protected $dates = ['deleted_at'];
    public $timestamps = false;

    protected $fillable = [
        'id', 'sch_name', 'sch_type', 'ext_type'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
