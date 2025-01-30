<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{

    protected $connection;

    protected $table = "permissions";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','PermissionType', 'DisplayName'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }
}
