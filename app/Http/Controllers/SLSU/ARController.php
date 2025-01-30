<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Exception;

use App\Models\user;
use App\Models\AccountBalances;
use App\Models\AccountPaid;
use App\Models\Registration;
use GENERAL;

class ARController extends Controller
{

  public function index(){

    $pageTitle = "Generate AR Table";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    return view('slsu.ar.index',[
    'pageTitle' => $pageTitle,
    'headerAction' => $headerAction
    ]);
  }

  public function generate(Request $request){
      try{

        //empty table first;
        $campus = $request->campus;
        $datefrom = $request->datefrom;
        $dateto = $request->dateto;

        AccountBalances::truncate();
        AccountPaid::truncate();
        $registrations = DB::connection(strtolower($campus))
            ->table('registration')
            ->select('RegistrationID', 'SchoolYear', 'Semester','Course')
            ->where("finalize",1)
            // ->where("StudentNo", "2390165-2")
            ->where("SchoolYear", ">=", 2009)
            // ->where("Semester", 1)
            ->where("forAR", ">=", 100)
            ->whereNotIn("SchoolLevel",['Masteral Vietnam','Doctoral Vietnam'])
            ->orderby('SchoolYear')
            ->orderby('Semester')
            ->get();
          // dd($registrations);
          foreach($registrations as $resReg){
              set_time_limit(0);
              // dd($resReg->Course);
              if ($resReg->SchoolYear == 2024 and $resReg->Semester == 2){

              }else{
                if($resReg->Course != "20160428173842"){
                  $this->ReAssess($resReg->RegistrationID, [
                    'DateFrom' => $datefrom,
                    'DateTo' => $dateto,
                    'Campus' => $campus
                  ]);
                }
              }

          }

      }catch(Exception $e){
          return response()->json(['errors' => 'Line '.$e->getLine().': '. $e->getMessage()],400);
      }catch(DecryptException $e){
          return response()->json(['errors' => $e->getMessage()],400);
      }
  }

  public function ReAssess($registrationID,$data){

    $reg = DB::connection(strtolower($data['Campus']))
        ->table('registration')
        ->select('registration.*', 'sl.Style', 'sc.amount as ScholarAmount',
        'sc.typ as ScholarType', 'sc.scholar_name',
        's.FirstName', 's.LastName', 'sl.AccountCode')
        ->leftjoin('students as s', 'registration.StudentNo', '=', 's.StudentNo')
        ->leftjoin('studentlevel as sl', 'registration.SchoolLevel', '=','sl.Description')
        ->leftjoin('scholar as sc', 'registration.Scholar', '=', 'sc.id')
        ->where('registration.RegistrationID', $registrationID)
        ->first();
    // dd($reg);
		$payor = $reg->StudentNo;
		$tblGrades = "grades".$reg->SchoolYear.$reg->Semester;

    $tblcc = "courseoffering".$reg->SchoolYear.$reg->Semester;

		$resSubjects = [];
    $labTuition = 0;
    if (!Schema::connection(strtolower($data['Campus']))->hasTable($tblGrades)) {
        throw new Exception("Invalid Table grades");
    }

    $subjects = DB::connection(strtolower($data['Campus']))
      ->table($tblGrades.' as g')
      ->select('t.units', 't.exempt', 'cc.proceed', 'cc.fee')
      ->leftjoin('transcript as t', 'g.sched', '=', 't.id')
      ->leftjoin($tblcc.' as cc', 'g.courseofferingid', '=', 'cc.id')
      ->where('g.gradesid', $registrationID)
      ->get();
    // dd($subjects);
    $units = 0;
    foreach($subjects as $subject){
      if ($subject->exempt != 1){
        $units += (empty($subject->units)?0:$subject->units);
      }
      $proceed = (isset($subject->proceed)?$subject->proceed:"");

      $fee = (isset($subject->fee)?$subject->fee:0);

      if ($fee > 0){
        if (str_contains($proceed, '644')) {
          $labTuition += $fee;
        }
        if (str_contains($proceed, '4020201000a')) {
          $labTuition += $fee;
        }
      }
    }

    if ($reg->Style == "pUnit"){
      $tuition = (empty($units)?0:$units) * (empty($reg->UnitCost)?0:$reg->UnitCost);
    }else{
      $tuition = $reg->UnitCost;
    }
//  or $reg->ScholarType == 3
    $total = 0;
		if ($reg->TES == 1 and $reg->SchoolLevel == "Under Graduate"){
        if ($reg->isUnifastPaid == 1){
          $total = 0;
        }else{
          $total = $tuition;
        }
		}elseif ($reg->ScholarType == 3){
      $amount = (empty($reg->ScholarAmount )?0:$reg->ScholarAmount);
      if ($amount <= 100 and $reg->SchoolLevel == "Under Graduate"){
        $total = 0;
      }else{
        if ($amount < $tuition){
          $total = $tuition - $amount;
        }else{
          $total = 0;
        }
      }
    }else{
			$sc = 0;
			if (!empty($reg->Scholar)){
				if ($reg->ScholarType == 1){
					$sc = ($tuition * ((empty($reg->ScholarAmount )?0:$reg->ScholarAmount) / 100));
				}else{
					$sc = (empty($reg->ScholarAmount )?0:$reg->ScholarAmount);
				}
				$total = ($tuition - ($sc <= 0 ?0 :$sc));
			}else{
				$total = $tuition;
			}
		}

    $req = $this->getRequested($payor, [
      'SchoolYear' => $reg->SchoolYear,
      'Semester' => $reg->Semester,
      'Campus' => $data['Campus']
    ]);

    $subtotal = $total + $req + $labTuition;
    $balance = 0;
    $date_for_aging = substr($reg->RegistrationID,0, 4)."-".substr($reg->RegistrationID,4, 2)."-".substr($reg->RegistrationID,6, 2);

    if ($subtotal >= 0){
      $paid = $this->getPaidFromTable($reg->StudentNo,[
        'SchoolYear' => $reg->SchoolYear,
        'Semester' => $reg->Semester,
        'Campus' => $data['Campus'],
        'DateFrom' => $data['DateFrom'],
        'DateTo' => $data['DateTo'],
        'Tuition' => $subtotal
      ]);

      // dd($paid);
      //$subtotal - $paid puhon if new AR
      if (!empty($paid)){
        //if first time,
        // $balance = $subtotal - $paid;
        //else
        if ($reg->SchoolYear == 2024 and $reg->Semester == 2){
          $balance = $reg->forAR;
        }else{
          $balance = $reg->forAR - $paid;
        }

      }else{
        //if first time, $balance = $subtotal;
        // $balance = $subtotal;

        //else
        $balance = $reg->forAR;
      }

    }

    // dd($balance);
    if ($balance > 0){
      $dataSave = [
        'SchoolYear' => $reg->SchoolYear,
        'Semester' => $reg->Semester,
        'AccountID' => $reg->AccountCode,
        'AccountDescription' => 'Tuition Fee',
        'Balance' => $balance,
        'StudentNo' => $payor,
        'Date' => $date_for_aging,
        'Tuition' => $subtotal,
        'Paid' => $paid, //if bag o pa, dapat $paid dapat value ani
        'Currentpaid' => 0, //if bag o pa, dapat 0 sa ni else $paid
      ];

      $save = DB::connection(strtolower($data['Campus']))
        ->table('accountbalances')
        ->insert($dataSave);

      if (!$save)
        return response()->json(['errors' => 'Unable to save to accountbalances'],400);
    }
    // ibalik ni later. sa krn ra na kay nag experiment
    $upreg = DB::connection(strtolower(session('campus')))
      ->table('registration')
      ->where("RegistrationID", $reg->RegistrationID)
      ->update([
        'forAR' => $balance
      ]);
	}

  public function getRequested($studentno = "", $data){
		set_time_limit(0);

    $sqlAdjust = DB::connection(strtolower($data['Campus']))
        ->table('adjust as a')
        ->select(DB::connection(strtolower($data['Campus']))->raw('sum(Amount) as sAmount'))
        ->leftjoin('transcript as t', 'a.CourseNo', '=', 't.id')
        ->where('a.StudentNo', $studentno)
        ->where('a.SchoolYear', $data['SchoolYear'])
        ->where('a.Semester', $data['Semester'])
        ->first();

    $out = 0;
    if (!empty($sqlAdjust->sAmount)){
      $out = $sqlAdjust->sAmount;
    }
    return $out;

	}

  private function getPaid($regid,$sy,$sem,$date = '', $code){
		set_time_limit(0);
		$tbl = "paid_assess_sub".$sy.$sem;

    try{
      if (!Schema::connection(strtolower(session('campus')))->hasTable($tbl)) {
          throw new Exception("Invalid Table paid assess sub");
      }

      $paid = DB::connection(strtolower(session('campus')))
        ->table($tbl.' as ps')
        ->select(DB::connection(strtolower(session('campus')))->raw('sum(Amount) as sAmount'))
        ->where('ps.registrationid', $regid)
        ->where('ps.AccountID', $code)
        ->where('ps.date_paid', '<=', $date)
        ->first();
      $out = 0;
      if (!empty($paid->sAmount)){
        $out = $paid->sAmount;
      }
      return $out;
    }catch(Exception $e){
      return response()->json(['errors' => $e->getMessage()], 400);
    }
	}

  private function getPaidFromTable($snum,$data){
		set_time_limit(0);
    $sum = 0;
    try{
      $tbl = "paid_assess_sub".$data['SchoolYear'].$data['Semester'];
      $paid_assess = "paid_assess".$data['SchoolYear'].$data['Semester'];
      $sum = DB::connection(strtolower($data['Campus']))
          ->table($tbl.' as psub')
          ->leftjoin($paid_assess.' as p', 'psub.paid_assess_id','=','p.ORCode')
          ->whereIn('AccountID', ['644-1','644-2','644-3','644-1A','4020201000a1','4020201000a2','4020201000a3'])
          ->where('Payor', $snum)
          ->whereBetween('psub.date_paid',[$data['DateFrom'],$data['DateTo']])
          ->sum('psub.Amount');

      if ($sum > 0)
      {
        $this->saveToPaidtable([
          'withMatched' => 1,
          'Tuition' => $data['Tuition'],
          'Paid' => $sum,
          'Balance' => $data['Tuition']-$sum,
          'Semester' => $data['Semester'],
          'SchoolYear' => $data['SchoolYear'],
          'StudentNo' => $snum,
        ]);
      }
      return $sum;
    }catch(Exception $e){
      $this->saveToPaidtable([
        'withMatched' => 3,
        'Tuition' => $data['Tuition'],
        'Paid' => 0,
        'Balance' => $data['Tuition'],
        'Semester' => $data['Semester'],
        'SchoolYear' => $data['SchoolYear'],
        'StudentNo' => $snum .' - '. $e->getMessage(),
      ]);
      return $sum;
    }
	}

  private function saveToPaidtable($data){
    AccountPaid::insert([
      'withMatched' => $data['withMatched'],
      'Tuition' => $data['Tuition'],
      'Paid' => $data['Paid'],
      'Balance' => $data['Balance'],
      'Semester' => $data['Semester'],
      'SchoolYear' => $data['SchoolYear'],
      'StudentNo' => $data['StudentNo'],
    ]);
  }
  // private function getPaidFromTable($snum,$sy,$sem){
	// 	set_time_limit(0);
  //   $sum = 0;
  //   try{
  //     $sum = AccountPaid::where("StudentNo",$snum)
  //       ->select('Amount')
  //       ->where("SchoolYear",$sy)
  //       ->where("Semester", $sem)
  //       ->sum('Amount');
  //     return $sum;
  //   }catch(Exception $e){
  //     return $sum;
  //   }
	// }

  public function paymenttuition(){
    $pageTitle = "Generate Collected Tuition Fee payment";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    return view('slsu.ar.tuitionfee',[
    'pageTitle' => $pageTitle,
    'headerAction' => $headerAction
    ]);
  }

  public function generatetuition(Request $request){
    try{

      AccountPaid::truncate();

      $campus = $request->campus;
      $datefrom = $request->datefrom;
      $dateto = $request->dateto;

      $request->validate([
        'campus' => 'required',
        'datefrom' => 'required',
        'dateto' => 'required'
      ]);

      $out = [];
      $tables = DB::connection(strtolower(session('campus')))
        ->select('SHOW TABLES LIKE "paid_assess_sub%"');
      foreach ($tables as $table) {
          foreach ($table as $key => $tbl){
              if ($tbl != "paid_assess_sub"){
                $sysem = (int) str_replace("paid_assess_sub","",$tbl);
                if(empty($sysem)){
                  break;
                }
                $sy = substr($sysem, 0, 4);
                $sem = substr($sysem, 4, strlen($sysem)-4);
                $paid_assess = "paid_assess".$sy.$sem;

                $ress = DB::connection(strtolower(session('campus')))
                    ->table($tbl.' as psub')
                    ->select('psub.*', 'p.Payor')
                    ->leftjoin($paid_assess.' as p', 'psub.paid_assess_id','=','p.ORCode')
                    ->where('AccountID', 'LIKE', '644%')
                    ->whereBetween('psub.date_paid',[$datefrom,$dateto])
                    ->get();
                foreach($ress as $res){
                  set_time_limit(0);
                  // $out[] = [
                  //   'DatePaid' => $res->date_paid,
                  //   'Amount' =>$res->Amount,
                  //   'Particular' => $res->item_name,
                  //   'AccountID' => $res->AccountID,
                  //   'Payor' => $res->Payor
                  // ];
                  $amount = (empty($res->Amount)?0:$res->Amount);
                  if ($res->Amount == "NaN.00"){
                    $amount = 0;
                  }

                  if ($res->Amount=="2731.71.00"){
                    $amount = 2731.71;
                  }

                  AccountPaid::insert([
                    'withMatched' => 0,
                    'Paid' => $amount,
                    'Semester' => $res->Semester,
                    'SchoolYear' => $res->SchoolYear,
                    'StudentNo' => $res->Payor
                  ]);
                }

              }

          }
      }
      return view('slsu.ar.tuitionfeeres', compact('out'));
    }catch(Exception $e){
      return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
    }
  }
}
