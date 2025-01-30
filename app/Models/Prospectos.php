<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Prospectos extends Model
{

    protected $connection;

    protected $table = "transcript";

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','courseno', 'coursetitle', 'semester', 'course','stud_year','flag','units',
        'fee','prerequisite','exempt','proceed','sort_order','major_in','cur_num','pri',
        'ifee','icode','lab','lec','hide','CreatedBy','DateCreated','SubjectLevel',
        'TitleAlias','isComputer','ExcludeinAVG','HideInTOR','corequisite','feefortes'
    ];

    public function __construct(){
        $this->connection = strtolower(session('campus'));
    }

}
