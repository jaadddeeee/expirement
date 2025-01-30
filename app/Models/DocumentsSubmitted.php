<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DocumentsSubmitted extends Model
{

    protected $connection;
    protected $table = "submitteddocuments";

    protected $fillable = [
        'id','DocumentID', 'StudentNo', 'AcceptedBy', 'SchoolYear', 'Semester'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
