<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultValue extends Model
{
    protected $connection;

    protected $table = 'defaultvalue';

    public $timestamps = false;

    protected $fillable = [
        'DefaultName',
        'DefaultValue'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
