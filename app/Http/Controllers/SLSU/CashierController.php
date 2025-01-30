<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

use Exception;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\Registration;
use GENERAL;

class CashierController extends Controller
{

    public function index(){

      $pageTitle = "Manage Payment";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.cashier.index',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
      ]);
    }

    public function searchpayment(Request $request){
        $str = $request->str;

        try{
            if (empty($str))
              throw new Exception("Empty search string");

              $dataTMP = explode(" - ", $str);

              if (sizeof($dataTMP) != 2)
                throw new Exception("Invalid Student format. ");

              $studentnumber = $dataTMP[0];
              $tmpStudent =  DB::connection(strtolower(session('campus')))
                ->table('students as s')
                ->select("s.*", "c.accro", "m.course_major")
                ->leftjoin("course as c", "s.Course", "=", "c.id")
                ->leftjoin("major as m", "s.major", "m.id")
                ->where("s.StudentNo", $studentnumber)
                ->first();

              if (empty($tmpStudent))
                throw new Exception("Student not found. ");



              $registrations = DB::connection(strtolower(session('campus')))->table('registration as r')
                ->leftjoin('scholar as s', 'r.Scholar', '=', 's.id')
                ->leftjoin('studentlevel as sl', 'r.SchoolLevel', '=','sl.Description')
                ->where("StudentNo", $studentnumber)
                ->orderBy("SchoolYear", "DESC")
                ->orderBy("Semester", "DESC")
                ->get();

              $allReg = [];
              foreach($registrations as $reg){

                  $cc = 'courseoffering'.$reg->SchoolYear.$reg->Semester;
                  $g = 'grades'.$reg->SchoolYear.$reg->Semester;
                  $assess = 'assessment'.$reg->SchoolYear.$reg->Semester;
                  $pMain = 'paid_assess'.$reg->SchoolYear.$reg->Semester;
                  $pSub = 'paid_assess_sub'.$reg->SchoolYear.$reg->Semester;

                  if (Schema::connection(strtolower(session('campus')))->hasTable($g)) {
                      $units = DB::connection(strtolower(session('campus')))->table($g.' as g')
                          ->select(DB::connection(strtolower(session('campus')))->raw('sum(t.units) as sumUnits'))
                          ->leftjoin('transcript as t', 'g.sched', '=', 't.id')
                          ->where("gradesid", $reg->RegistrationID)
                          ->where('t.exempt', '<>', 1)
                          ->first();

                      $Lab = DB::connection(strtolower(session('campus')))->table($g.' as g')
                          ->select(DB::connection(strtolower(session('campus')))->raw('sum(cc.fee) as sumLab'))
                          ->leftjoin($cc.' as cc', 'g.courseofferingid', '=', 'cc.id')
                          ->where("gradesid", $reg->RegistrationID)
                          ->first();

                      $GenFees = DB::connection(strtolower(session('campus')))->table($assess.' as g')
                          ->select(DB::connection(strtolower(session('campus')))->raw('sum(g.amount) as sumGenFees'))
                          ->where("registrationid", $reg->RegistrationID)
                          ->first();

                      $requested = DB::connection(strtolower(session('campus')))->table('adjust')
                      ->select(DB::connection(strtolower(session('campus')))->raw('sum(Amount) as sumRequested'))
                      ->where("SchoolYear", $reg->SchoolYear)
                      ->where("Semester", $reg->Semester)
                      ->where("StudentNo", $studentnumber)
                      ->first();

                      $paid = DB::connection(strtolower(session('campus')))->table($pMain.' as pMain')
                          ->select(DB::connection(strtolower(session('campus')))->raw('sum(pSub.Amount) as sumPaid'))
                          ->leftjoin($pSub .' as pSub', 'pMain.ORCode', '=','pSub.paid_assess_id')
                          ->leftjoin('chartaccount as ca','pSub.AccountID','=','ca.AccountID')
                          ->where('pMain.Payor', $studentnumber)
                          ->where('ca.forStudent', 1)
                          ->first();

                      $Style = $reg->Style;
                      $tuition = ($reg->Style == "pUnit"?(empty($reg->UnitCost)?0:str_replace(',','',$reg->UnitCost))*$units->sumUnits:str_replace(',','',$reg->UnitCost));
                      $UnitCost = (empty($reg->UnitCost)?0:str_replace(',','',$reg->UnitCost));
                      $Units = $units->sumUnits;

                      $reqAmount = (empty($requested->sumRequested)?0:$requested->sumRequested);
                      $dumFees = (empty($GenFees->sumGenFees) ? 0: $GenFees->sumGenFees);
                      $sumLab = (empty($Lab->sumLab)?0:$Lab->sumLab);
                      $tuition = (empty($tuition)?0:$tuition);

                      $SubTotal = $dumFees +  $sumLab  + $reqAmount + $tuition;
                      $less = 0;
                      if ($reg->TES == 1){
                          $sch = "TES";
                          $less = $SubTotal - $reqAmount;
                      }else{
                          $sch = $reg->scholar_name;
                          if ($reg->typ == 1){
                              $less = $tuition * (str_replace("%","",$reg->amount)/100);
                          }elseif ($reg->typ == 2){
                              $less = $reg->amount;
                          }elseif ($reg->typ == 3){
                              $less = $SubTotal - $reqAmount;
                          }
                      }

                      $AmountDue = $SubTotal - $less;
                      $balance = $AmountDue-(!isset($paid->sumPaid)?0:$paid->sumPaid);

                      Registration::where("StudentNo", $studentnumber)
                        ->where("SchoolYear", $reg->SchoolYear)
                        ->where("Semester", $reg->Semester)
                        ->update([
                          'Balance' => $balance
                        ]);

                      array_push($allReg, [
                          'RegistrationID' => $reg->RegistrationID,
                          'Finalize' => $reg->finalize,
                          'SchoolYear'=>$reg->SchoolYear,
                          'Semester'=>$reg->Semester,
                          'GeneralFees'=>number_format($dumFees, 2,'.',','),
                          'LabFees'=>number_format( $sumLab , 2,'.',','),
                          'Requested'=>number_format($reqAmount, 2,'.',','),
                          'TuitionFee'=>number_format($tuition, 2,'.',','),
                          'SubTotal'=>number_format($SubTotal, 2,'.',','),
                          'Less'=>number_format($less, 2,'.',','),
                          'Due'=>number_format($AmountDue, 2,'.',','),
                          'Paid'=>(!isset($paid->sumPaid)?"0.00":number_format($paid->sumPaid, 2,'.',',')),
                          'Balance'=>number_format($balance, 2,'.',','),
                          'Style' => $Style,
                          'UnitCost' => $UnitCost,
                          'Unit' => $Units,
                          'ScholarName' => $sch
                      ]);
                  }
              }

              return view('slsu.cashier.feematrix', compact('allReg','tmpStudent'));

        }catch(Exception $e){
            return GENERAL::Error($e->getMessage());
        }
    }

    public function searchgenfee(Request $request){
        try{
            $id = Crypt::decryptstring($request->sid);

            $reg = Registration::where("RegistrationID", $id)->first();
            if (empty($reg))
              throw new Exception("Invalid ID. Registration not found.");

            $assess = "assessment".$reg->SchoolYear.$reg->Semester;
            if (!Schema::connection(strtolower(session('campus')))->hasTable($assess))
              throw new Exception("Invalid Data. Table not found");

            $fees = DB::connection(strtolower(session('campus')))->table($assess)
              ->where("registrationid", $id)
              ->get();
            return view('slsu.cashier.generalfees', compact('fees'),[
              'ID' => $request->sid,
              'Table' => Crypt::encryptstring($assess)
            ]);
        }catch(Exception $e){
          return GENERAL::Error($e->getMessage());
        }catch(DecryptException $e){
          return GENERAL::Error("Invalid ID.");
        }
    }

    public function savefee(Request $request){
      try{
          $id = Crypt::decryptstring($request->hiddenID);
          $table = Crypt::decryptstring($request->hiddenTable);
          $allfees = $request->allfees;
          $amount = $request->amount;

          $reg = Registration::where("RegistrationID", $id)->first();
          if (empty($reg))
            throw new Exception("Invalid ID. Registration not found.");

          if (empty($amount))
            throw new Exception("Invalid Amount.");

          if (!Schema::connection(strtolower(session('campus')))->hasTable($table))
            throw new Exception("Invalid Data. Table not found");

          $dataTMP = explode(" - ", $allfees);

          if (sizeof($dataTMP) != 2)
            throw new Exception("Invalid Fee format.");

          $feeCode = trim($dataTMP[0]);
          $description = $dataTMP[1];

          $ex = DB::connection(strtolower(session('campus')))->table($table)
            ->where("AccountID", $feeCode)
            ->where("registrationid", $id)
            ->first();

          if (!empty($ex))
            throw new Exception("Fee already exist for ".$feeCode. " (".$description.")");

          $feeID = date('Ymd').time();
          $data = [
            'id' => $feeID,
            'registrationid' => $id,
            'item' => trim($description),
            'amount' => str_replace(",","",$amount),
            'AccountID' => $feeCode
          ];

          $res = DB::connection(strtolower(session('campus')))->table($table)
              ->insert($data);

          if ($res){
              $log = new LogController();
              $log->fee([
                'StudentNo' => $reg->StudentNo,
                'ChartAccountID' => $feeCode,
                'Amount' => str_replace(",","",$amount),
                'CurrentUser' => auth()->user()->Emp_No,
                'Action' => "Add",
                'OldAmount' => 0,
                'Flag' => "fee",
                'Table' => $table,
                'Description' => trim($description)
              ]);

              return response()->json([
                'Error' => 0,
                'Message' => $request->hiddenID
              ]);
          }

          throw new Exception("Unable to save fee");
      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
      }catch(DecryptException $e){
          return response()->json([
            'Error' => 1,
            'Message' => "Invalid ID"
          ]);
      }
    }

    public function deletefee(Request $request){
      try{
          $id = Crypt::decryptstring($request->id);
          $rid = Crypt::decryptstring($request->rid);

          if (empty($id))
            throw new Exception("Invalid ID");

          if (empty($rid))
            throw new Exception("Invalid ID. Registration not found.");

          $reg = Registration::where("RegistrationID", $rid)->first();
          if (empty($reg))
            throw new Exception("Invalid ID. Registration not found.");

          $table = "assessment".$reg->SchoolYear.$reg->Semester;
          if (!Schema::connection(strtolower(session('campus')))->hasTable($table))
            throw new Exception("Invalid Data. Table not found");


          $ex = DB::connection(strtolower(session('campus')))->table($table)
            ->where("id", $id)
            ->where("registrationid", $rid)
            ->first();

          if (empty($ex))
            throw new Exception("Invalid Fee. Fee not found.");

          $res = DB::connection(strtolower(session('campus')))->table($table)
              ->where("id", $id)
              ->where("registrationid", $rid)
              ->delete();

          if ($res){
              $log = new LogController();
              $log->fee([
                'StudentNo' => $reg->StudentNo,
                'ChartAccountID' => $ex->AccountID,
                'Amount' => $ex->amount,
                'CurrentUser' => auth()->user()->Emp_No,
                'Action' => "Delete",
                'OldAmount' => 0,
                'Flag' => "fee",
                'Table' => $table,
                'Description' => $ex->item
              ]);

              return response()->json([
                'Error' => 0,
                'Message' => ""
              ]);
          }

          throw new Exception("Unable to save fee");
      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
      }catch(DecryptException $e){
          return response()->json([
            'Error' => 1,
            'Message' => "Invalid ID"
          ]);
      }
    }

    public function getrecord(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

        $reg = Registration::select("registration.*",'sc.scholar_name')
            ->leftjoin("scholar as sc", "registration.Scholar", "=", "sc.id")
            ->where("RegistrationID", $id)->first();

        if (empty($reg))
          throw new Exception("Invalid ID. Registration not found.");

        $sch = "NONE";

        if ($reg->TES == 1){
          $sch = "TES";
        }elseif (!empty($reg->Scholar)){
          $sch = $reg->scholar_name;
        }

        return response()->json([
          'Error' => 0,
          'Message' => $sch
        ]);

        throw new Exception("Unable to check scholarship");
      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
      }catch(DecryptException $e){
          return response()->json([
            'Error' => 1,
            'Message' => "Invalid ID"
          ]);
      }
    }

    public function updatescholar(Request $request){
      try{
        $id = Crypt::decryptstring($request->hiddenID);
        $sch = $request->allschs;

        if (empty($sch))
          throw new Exception("Invalid Data. No scholarship selected.");

        $reg = Registration::where("RegistrationID", $id)->first();

        if (empty($reg))
          throw new Exception("Invalid ID. Registration not found.");

        if ($reg->TES == 1){
          throw new Exception("Student is already a TES. Cannot update scholarship.");
        }

        $dataTMP = explode(" - ", $sch);

        if (sizeof($dataTMP) != 2)
          throw new Exception("Invalid Scholarship format.");

        $name = trim($dataTMP[0]);
        $amount = trim($dataTMP[1]);

        $ex = Scholarship::where("scholar_name",$name)
          ->where("amount", $amount)->first();
        if (empty($ex)){
          throw new Exception("No scholarship found.");
        }


        $update = Registration::where("RegistrationID", $id)
          ->update([
            'Scholar' => $ex->id
          ]);

        if ($update){
          $log = new LogController();
          $log->fee([
            'StudentNo' => $reg->StudentNo,
            'ChartAccountID' => $ex->id,
            'Amount' => str_replace(",","",$amount),
            'CurrentUser' => auth()->user()->Emp_No,
            'Action' => "Update",
            'OldAmount' => 0,
            'Flag' => "scholarship",
            'Table' => '',
            'Description' => trim($name),
            'OldScholar' => ($reg->TES == 1?"TES":$reg->Scholar)
          ]);
          return response()->json([
            'Error' => 0,
            'Message' => ""
          ]);
        }

        throw new Exception("Unable to check scholarship");

      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
      }catch(DecryptException $e){
          return response()->json([
            'Error' => 1,
            'Message' => "Invalid ID"
          ]);
      }

    }
}


