<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Exception;
use GENERAL;
use App\Models\Employee;

class TrackerController extends Controller
{

    public function gradesheet($data){

      $ex = DB::connection(strtolower(session('campus')))
        ->table("trackgradesheet")
        ->where("EmployeeID", $data['EmployeeID'])
        ->where("CourseOfferingID", $data['CourseOfferingID'])
        ->where("SchoolYear", $data['SchoolYear'])
        ->where("Semester", $data['Semester'])
        ->first();

      if (empty($ex))
      $sve = DB::connection(strtolower(session('campus')))
        ->table("trackgradesheet")
        ->insert($data);
    }

    public function listgradesheet(){
      $pageTitle = "Track Gradesheet";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.tracker.gradesheet', [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function searchgradesheet(Request $request){
        $emp = $request->allemp;
        $SchoolYear = $request->SchoolYear;
        $Semester = $request->Semester;

        try{
            if (empty($emp))
              throw new Exception("Empty Employee Name");

            if (empty($SchoolYear))
              throw new Exception("Empty School Year");

            if (empty($Semester))
              throw new Exception("Empty Semester");

              $dataTMP = explode(",", $emp);

            if (sizeof($dataTMP) != 2)
              throw new Exception("Invalid Student format. ");

            $FirstName = trim($dataTMP[1]);
            $LastName = trim($dataTMP[0]);

            $one = Employee::where("LastName", $LastName)
              ->where("FirstName", $FirstName)
              ->where("isActive", "Yes")
              ->first();

            if (empty($one))
              throw new Exception("Invalid Employee");

            $table = "courseoffering".$SchoolYear.$Semester;
            if (!Schema::connection(strtolower(session('campus')))->hasTable($table))
              throw new Exception("Invalid SchoolYear or  Semester");

            $ex = DB::connection(strtolower(session('campus')))
                ->table($table. " as cc")
                ->select("e.LastName", "e.FirstName","t.courseno", "t.coursetitle", "cc.coursecode","tg.DateGenerated", "tg.id", "tg.DateAccepted", "tg.AcceptedBy")
                ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
                ->leftjoin("trackgradesheet as tg", "cc.id", "=", "tg.CourseOfferingID")
                ->leftjoin("employees as e", "tg.AcceptedBy", "e.id")
                ->where("teacher", $one->id)
                ->get();

            if (count($ex) <= 0)
              throw new Exception("Employee has no workload on semected preference");


            return view('slsu.tracker.listgradesheet', compact('ex'));

        }catch(Exception $e){
            return GENERAL::Error($e->getMessage());
        }

    }

    public function acceptgradesheet(Request $request){

        try{

          $id = Crypt::decryptstring($request->id);
          if (empty($id))
            throw new Exception("Invalid ID");

          $ex = DB::connection(strtolower(session('campus')))
              ->table("trackgradesheet")
              ->where("id", $id)
              ->update([
                'AcceptedBy' => auth()->user()->Emp_No,
                'DateAccepted' => date('Y-m-d')
              ]);

          if (!$ex)
            throw new Exception("Unable to accept gradesheet. Please try again");


          return response()->json([
            'Error' => 0,
            'Message' => ''
          ]);

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

    public function nosubmitgradesheet(){
      try{

        $sy = session('schoolyear');
        $sem = session('semester');

        $cc = "courseoffering".$sy.$sem;
        if (!Schema::connection(strtolower(session('campus')))->hasTable($cc)){
          throw new Exception("Invalid Settings");
        }

        $countAccepted = DB::connection(strtolower(session('campus')))
        ->table("trackgradesheet")
        ->whereNotNull("AcceptedBy")
        ->where("SchoolYear", $sy)
        ->where("Semester", $sem)
        ->pluck('CourseOfferingID')->toArray();

        $CClists = DB::connection(strtolower(session('campus')))
            ->table($cc." as cc")
            ->select('cc.*', 'e.FirstName', 'e.LastName', 'st.tym')
            ->leftjoin('employees as e', 'cc.teacher', '=', 'e.id')
            ->leftjoin('schedule_time as st', 'cc.sched', '=', 'st.id')
            ->where("cc.teacher", ">", 0)
            ->whereNotIn("cc.id", $countAccepted)
            ->orderby('e.LastName')
            ->orderBy('e.FirstName')
            ->get();

        $pageTitle = "No Submitted Gradesheet Masterlist";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.tracker.nosubmitgradesheet', compact('CClists','sy','sem'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
        ]);

      }catch(Exception $e){
        return "Error: ".$e->getMessage();
      }


    }
}
