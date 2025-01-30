<?php
namespace App\Http\Controllers\SLSU;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GradeLock extends Controller{

	protected $sy;
	protected $sem;
	protected $DateEncode;
	protected $DateEnd;
	protected $DateSet;
	protected $AddedBy;
	protected $Ext = 0;
	protected $CExt = 0;
	protected $teacherid;
	protected $schedid;
	protected $global;
	protected $db;

	function __construct($request){
    $this->sy = $request['sy'];
    $this->sem = $request['sem'];
		$this->query();
	}

	function setSchedID($id = ""){
		$this->schedid = $id;
	}

	function getSchedID(){
		return $this->schedid;
	}

	function setTeacherID($id = ""){
		$this->teacherid = $id;
	}

	function getTeacherID(){
		return $this->teacherid;
	}

	function query()
	{

    $res = DB::connection(strtolower(session('campus')))->table("gradepreference")
        ->where("SchoolYear", $this->sy)
        ->where("Semester", $this->sem)
        ->first();

		$this->setDateStart(isset($res->StartEncode)?$res->StartEncode:"");
		$this->setDateEnd(isset($res->EndEncode)?$res->EndEncode:"");
		$this->setDateSet(isset($res->DateSet)?$res->DateSet:"");
		$this->setAddedBy(isset($res->AddedBy)?$res->AddedBy:"");

	}

	function isOKToEncode(){

			if (empty($this->getDateStart()) or empty($this->getDateEnd())){
				return false;
			}else{
				$dateToday = date('Y-m-d');
				if ($dateToday >= $this->getDateStart() and $dateToday <= $this->getDateEnd())
				{
					return true;
				}else{
					return false;
				}
			}
	}

	function isOKToChange(){

		$ds = date('Y-m-d', strtotime($this->getDateEnd(). ' + 1 days'));

		$Date = $this->getDateEnd();
		$eDate = date('Y-m-d', strtotime($Date. ' + '.$this->getChangeExtension().' days'));

		$dateToday = date('Y-m-d');
		if ($dateToday >= $ds and $dateToday <= $eDate)
		{
			return true;
		}else{
			return false;
		}
	}

	function getDateExtension(){
		return date('Y-m-d', strtotime($this->getDateEnd(). ' + '.$this->getChangeExtension().' days'));
	}

	function setColor(){
		if ($this->isOKToEncode()){
			return 'style="background-color:#258B44; padding: 2px; color: #FFFFFF"';
		}else{
			return 'style="background-color:#FF0000; padding: 2px; color: #FFFFFF"';
		}
	}

	function setChangeExtension($ext){
		$this->CExt = $ext;
	}

	function getChangeExtension(){
		return $this->CExt;
	}

	function setExtension($ext){
		$this->Ext = $ext;
	}

	function getExtension(){
		return $this->Ext;
	}

	function setDateStart($d){
		$this->DateEncode = $d;
	}

	function getDateStart(){
		return $this->DateEncode;
	}

	function setDateEnd($de){
		$this->DateEnd = $de;
	}

	function getDateEnd(){
		if  (empty($this->getSchedID())){
			return $this->DateEnd;
		}else{
			$tbl = "courseoffering".$this->sy.$this->sem;
      $res = DB::connection(strtolower(session('campus')))->table($tbl)
          ->select("LockExtend")
          ->where("teacher", $this->getTeacherID())
          ->where("id", $this->getSchedID())
          ->first();

			if ($res->LockExtend == "0000-00-00" or empty($res->LockExtend)){
				// return $res['LockExtend'];
				return $this->DateEnd;
			}elseif (empty($res->LockExtend)){
				return $this->DateEnd;
			}else{
				return $res->LockExtend;
			}
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
