<?php

namespace App\Models\VARSITY;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Varsity extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;

    protected $table = "var_varsity";
       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'FirstName',
        'MiddleName',
        'LastName',
        'VarsityEvent',
        'SchoolYear',
        'Semester',
        'Campus',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'VarsityEvent'); // Assuming 'coach_event' is the foreign key
    }

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
