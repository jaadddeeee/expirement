<?php

namespace App\Models\VARSITY;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoachVarsity extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = "var_scuaa_coach";
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'CoachID',
        'Event',
        'SchoolYear',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'Event', 'id');
    }

    public function coach()
    {
        return $this->belongsTo(Coach::class, 'CoachID', 'EmpNo');
    }



    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
