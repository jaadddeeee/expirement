<?php

namespace App\Http\Controllers\SLSU;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use ROLE;

class RegistrationLock extends Controller{

	protected $sy;
	protected $sem;
	protected $DateEncode;
	protected $DateEnd;
	protected $DateSet;
	protected $AddedBy;
	protected $Ext = 0;
	protected $CExt = 0;

	function __construct($request){
		$this->sy = $request['sy'];
    $this->sem = $request['sem'];
		$this->query();
	}

	function query()
	{

    $res = DB::connection(strtolower(session('campus')))->table("lockenrolment")
        ->where("SchoolYear", $this->sy)
        ->where("Semester", $this->sem)
        ->first();

		$this->setDateStart(isset($res->StartEncode)?$res->StartEncode:"");
		$this->setDateEnd(isset($res->EndEncode)?$res->EndEncode:"");
		$this->setDateSet(isset($res->DateSet)?$res->DateSet:"");
		$this->setAddedBy(isset($res->AddedBy)?$res->AddedBy:"");

	}

	function isOKToEncode(){

		if (auth()->user()->AllowSuper == 1){
			return true;
		}else{

			if (empty($this->getDateStart()) or empty($this->getDateEnd())){

				return false;
			}else{

				$dateToday = date('Y-m-d');
				//echo $this->getDateStart();
				if ($dateToday >= $this->getDateStart() and $dateToday <= $this->getDateEnd())
				{
					return true;
				}else{
					return false;
				}
			}
		}

		return false;
	}

	function setDateStart($d){
		//echo $d;
		$this->DateEncode = $d;
	}

	function getDateStart(){
		return $this->DateEncode;
	}

	function setDateEnd($de){
		$this->DateEnd = $de;
	}

	function getDateEnd(){

    $res = DB::connection(strtolower(session('campus')))
        ->table("accountsuser")
        ->where("UserName", auth()->user()->UserName)
        ->first();

		if ($res->ExtendEnrol == "0000-00-00"){
			return $this->DateEnd;
		}elseif (empty($res->ExtendEnrol)){
			return $this->DateEnd;
		}else{
			return $res->ExtendEnrol;
		}
	}

	function setDateSet($ds){
		$this->DateSet = $ds;
	}

	function getDateSet(){
		return $this->DateSet;
	}

	function setAddedBy($ab){
		$this->AddedBy = $ab;
	}

	function getAddedBy(){
		return $this->AddedBy;
	}


}
?>
