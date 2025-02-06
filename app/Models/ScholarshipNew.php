<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipNew extends Model
{
    use HasFactory;

    protected $table = "scholarship_new";
    public $timestamps = false;

    protected $fillable = [
        'id', 'sch_name', 'sch_type', 'ext_type'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
