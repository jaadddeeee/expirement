<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DocumentsRequired extends Model
{

    protected $connection;
    protected $table = "requireddocuments";

    protected $fillable = [
        'id','Description', 'StudentStatus'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
