<?php

namespace App\Models\Varsity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scuaa extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection;
    protected $table = "var_scuaa";
    protected $fillable = [
        'id','Title', 'ScuaaLogo', 'University', 'Location','Date'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
