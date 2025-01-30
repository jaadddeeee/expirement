<?php

namespace App\Http\Controllers\SLSU\Super;

use App\Http\Controllers\SLSU\SMSController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Preferences;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Role;
use Exception;
use GENERAL;

class SuperAdminController extends Controller
{
    public function preferences(){

      $pageTitle = "Preferences";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.super.preferences',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
          ]);
    }

    public function view(Request $request){

        $campus = (isset($request->Campus)?$request->Campus:"");
        if (empty($campus))
            return response()->json(['Error' => 1, "Message" => "Invalid Campus"]);

        $prefs = DB::connection(strtolower($campus))->table("defaultvalue")->get();

        return view("slsu.super.viewpreferences", compact('prefs'),[
          'Campus' => $campus
        ]);


    }

    public function save(Request $request){
      $ids = (isset($request->ids)?$request->ids:"");
      $campus = \Crypt::decryptstring($request->campus);

      if (empty($ids))
        return response()->json(['Error'=>1,"Message"=>"Invalid IDs"]);

      if (empty($campus))
        return response()->json(['Error'=>1,"Message"=>"Invalid Campus"]);

      $data = [];
      foreach($ids as $tmpid){
          $id = \Crypt::decryptstring($tmpid);
          $val = "pref-".$id;
          array_push($data, ['id'=>$id, 'DefaultValue' => $request->$val]);

      }
      $pref = new Preferences();
      $pref->setConnection($campus);
      if ($pref->upsert($data, "id"))
        return response()->json(['Error'=>0,"Message"=>""]);

      return response()->json(['Error'=>1,"Message"=>"Changes not saved"]);

    }


    public function users(Request $request){
      $pageTitle = "User Accounts";
      if (auth()->user()->AllowSuper == 1){
        $campus = "SG";
        $user = auth()->user()->Emp_No;
        if (isset($request->Campus)){
          $campus = $request->Campus;
          $user = (isset($request->Employee)?$request->Employee:$user);
        }
      }else{
        $campus = session('campus');
        $emp = (session('lastEmployee') !== null?session('lastEmployee'):'');
        $user = (isset($request->Employee)?$request->Employee:$emp);
      }


      $employees = DB::connection(strtolower($campus))
        ->table("accountsuser as au")
        ->select("e.*")
        ->leftjoin("employees as e", "au.Emp_No", "=", "e.id")
        ->where("au.Active", 1)
        ->wherenotnull("e.id")
        ->orderby("e.LastName")
        ->orderby("e.FirstName")
        ->get();

      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
      $permissions = DB::connection(strtolower($campus))
          ->table("permissions")
          ->orderby("DisplayName")->get();

      $subType = DB::connection(strtolower($campus))
          ->table("clearance_subtype as cs")
          ->get();

      $departments = DB::connection(strtolower($campus))
        ->table("department")
        ->get();



      $assignedprograms = DB::connection(strtolower(session('campus')))
            ->table("accountcourse as ac")
            ->select("ac.*", 'c.accro')
            ->leftjoin('course as c', 'ac.CourseID', 'c.id')
            ->where("UserName", $user)
            ->get();

        $courses = DB::connection(strtolower($campus))
        ->table("course")
        ->where('isActive', 0)
        ->whereNotIn('id', $assignedprograms->pluck('CourseID')->toArray())
        ->orderby('accro')
        ->get();

      return view('slsu.super.users',compact('permissions','employees','subType','departments','assignedprograms','courses'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Campus' => $campus,
            "Employee" => $user
          ]);
    }

    public function clearance(){
      $pageTitle = "Clearance";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.super.clearance',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
          ]);
    }

    public function proclearance(Request $request){

          try{
              $SchoolYear = $request->SchoolYear;
              $Semester = $request->Semester;
              $Campus = $request->Campus;

              if (empty($SchoolYear))
                throw new Exception('Please select School Year');

              if (empty($Semester))
                throw new Exception('Please select Semester');

              if (empty($Campus))
                throw new Exception('Please select Campus');

              $cleared = true;

              $regs = DB::connection(strtolower($Campus))->table("registration")
                  ->select("StudentNo")
                  ->where("finalize",1)
                  ->where("SchoolYear", $SchoolYear)
                  ->where("Semester", $Semester)
                  ->get();

              foreach($regs as $reg){
                set_time_limit(0);
                $ssc = DB::connection(strtolower($Campus))->table("fines")
                  ->select(DB::connection(strtolower($Campus))->raw("sum(Amount) as sumSSC"))
                  ->where("StudentNo", $reg->StudentNo)
                  ->where("paid", 0)
                  ->where("UserFlag", "SSC")
                  ->first();

                if (!empty($ssc->sumSSC))
                  $cleared = false;

                $sscobs = DB::connection(strtolower($Campus))->table("clearance_obligations")
                  ->where("StudentNo", $reg->StudentNo)
                  ->whereNull("deleted_at")
                  ->where("UserFlag", "SSC")
                  ->get();

                if (count($sscobs) > 0)
                  $cleared = false;

                $deptfine = DB::connection(strtolower($Campus))->table("fines")
                  ->select(DB::connection(strtolower($Campus))->raw("sum(Amount) as sumDept"))
                  ->where("StudentNo", $reg->StudentNo)
                  ->where("paid", 0)
                  ->where("UserFlag", "Department")
                  ->first();

                if (!empty($deptfine->sumDept))
                  $cleared = false;

                $obs = DB::connection(strtolower($Campus))->table("clearance_obligations")
                  ->where("StudentNo", $reg->StudentNo)
                  ->whereNull("deleted_at")
                  ->where("UserFlag", "Department")
                  ->get();

                if (count($obs) > 0)
                  $cleared = false;

                $libs = DB::connection(strtolower($Campus))->table("clearance_library")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();

                if (count($libs) > 0)
                  $cleared = false;

                $regs = DB::connection(strtolower($Campus))->table("clearance_registrar")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();

                if (count($regs) > 0)
                  $cleared = false;

                $bargos = DB::connection(strtolower($Campus))->table("clearance_bargo")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();
                if (count($bargos) > 0)
                  $cleared = false;

                $osass = DB::connection(strtolower($Campus))->table("clearance_osas")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();

                if (count($osass) > 0)
                  $cleared = false;

                $depts = DB::connection(strtolower($Campus))->table("clearance_department")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();

                if (count($depts) > 0)
                  $cleared = false;

                $miss = DB::connection(strtolower($Campus))->table("clearance_mis")
                  ->where("StudentNo", $reg->StudentNo)
                  ->get();

                if (count($miss) > 0)
                  $cleared = false;

                $cashiers = DB::connection(strtolower($Campus))->table("registration")
                  ->select("Balance")
                  ->where("StudentNo", $reg->StudentNo)
                  ->where("Balance", ">" , 0)
                  ->where("finalize", 1)
                  ->get();

                $sumCashier = $cashiers->sum("Balance");

                if ($sumCashier>0)
                  $cleared = false;

                $clr = 1;
                if (!$cleared)
                  $clr = 2;

                $Registration = DB::connection(strtolower($Campus))->table("registration")
                    ->where("StudentNo", $reg->StudentNo)
                    ->update([
                      'isCleared' => $clr
                    ]);
              }

              return GENERAL::Success("DONE");
          }catch(Exception $e){
              return GENERAL::Error($e->getMessage());
          }
    }

    public function startsearch(Request $request){

      $pageTitle = "Global Search";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.super.globalprosearch',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'str' => $request->str
      ]);

    }

    public function globalsearch(Request $request){

        $out = [];
        $str = $request->str;
        // dd($str);
        foreach(GENERAL::Campuses() as $index => $campus){
            $res = DB::connection(strtolower($index))
                ->table("students as s")
                ->select('s.StudentNo','s.FirstName','s.MiddleName','s.LastName','c.accro','s.cur_num')
                ->leftjoin("course as c", "s.Course", "=", "c.id")
                ->where(function($query) use ($str){
                  $query->orwhere("LastName","LIKE","%{$str}%")
                    ->orwhere("FirstName","LIKE","%{$str}%")
                    ->orwhere("StudentNo","LIKE","%{$str}%");
              })
              ->get();

            if (sizeof($res)> 0){
                foreach($res as $r){
                    array_push($out,[
                      "StudentNo" =>$r->StudentNo,
                      "FirstName" => $r->FirstName,
                      'MiddleName' => $r->MiddleName,
                      "LastName" => $r->LastName,
                      "Course" => $r->accro,
                      'Campus' => $campus['Campus'],
                      'CampusIndex' => $index,
                      'CurNum' => $r->cur_num
                    ]);
                }

            }
        }

        return view('slsu.super.globalsearch', compact('out'),[
          'str' => $str
        ]);
    }

    public function employeecampus(Request $request){
        try{
            $campus = $request->Campus;
            if (empty($campus))
              throw new Exception("Please select campus.");

            $employees = DB::connection(strtolower($campus))
                ->table("accountsuser as au")
                ->select("e.*")
                ->leftjoin("employees as e", "au.Emp_No", "=", "e.id")
                ->where("au.Active", 1)
                ->wherenotnull("e.id")
                ->orderby("e.LastName")
                ->orderby("e.FirstName")
                ->get();

            return response()->json($employees);
        }catch(Eeception $e){
            return response()->json([
              'Error' => 1,
              'Message' => $e->getMessage()
            ]);
        }
    }

    public function savepermission(Request $request){

        try{
          $out = [];
          $tosave = [];
          $id = $request->hiddenID;
          if (auth()->user()->AllowSuper == 1)
            $campus = $request->Campus;
          else
            $campus = session('campus');

          if (empty($id))
            throw new Exception("Invalid ID");
          if (empty($request->setPer))
            throw new Exception("Nothing is selected");
          if (empty($campus))
            throw new Exception("No campus is selected");

          $del = DB::connection(strtolower($campus))
              ->table("accountrole")
              ->where("EmpID", $id)->delete();

            foreach($request->setPer as $chk){
                if (strtolower($chk) == "department"){
                    if (empty($request->Department)){
                      array_push($out, "No department selected. Not saved for department permission.");
                    }else{
                      array_push($tosave,[
                        'EmpID' => $id,
                        'Role' => $chk,
                        'DepartmentID' => $request->Department,
                        'ClearanceRole' => NULL
                      ]);
                    }

                }elseif (strtolower($chk) == "clearance"){
                  if (empty($request->ClearanceType)){
                    array_push($out, "No clearance type selected. Not saved for clearance permission.");
                  }elseif (strtolower($request->ClearanceType) == "department"){
                      if (empty($request->Department2)){
                        array_push($out, "No department selected for department sub type. Not saved for clearance permission.");
                      }else{
                        array_push($tosave,[
                          'EmpID' => $id,
                          'Role' => $chk,
                          'DepartmentID' => $request->Department2,
                          'ClearanceRole' => $request->ClearanceType
                        ]);
                      }
                  }else{
                    array_push($tosave,[
                      'EmpID' => $id,
                      'Role' => $chk,
                      'ClearanceRole' => $request->ClearanceType,
                      'DepartmentID' => 0
                    ]);
                  }
                }else{
                  array_push($tosave,[
                    'EmpID' => $id,
                    'Role' => $chk,
                    'ClearanceRole' => NULL,
                    'DepartmentID' => 0
                  ]);
                }
            }

            // dd($tosave);
            DB::connection(strtolower($campus))
              ->table("accountrole")
              ->insert($tosave);

            session(['ErrorPermission' => implode("<br>", $out)]);

        }catch(Exception $e){
            session(['ErrorPermission' => $e->getMessage()]);
            return redirect()->route('all-users');
        }

        if (auth()->user()->AllowSuper == 0){
          session([
            'lastEmployee' => $id
          ]);
        }
        return redirect()->route('all-users');


    }

    public function assignedprogram(Request $request){
        try{
          if (!$request->ajax()){
            throw new Exception("Invalid request");
          }

          $request->validate([
            'hidEmployeeID' => 'required',
            'newprogram' => 'required',
            'hidCampus' =>'required',
          ]);

          $campus = $request->hidCampus;

          $assignedprograms = DB::connection(strtolower($campus))
              ->table("accountcourse")
              ->where("CourseID", $request->newprogram)
              ->where("UserName", $request->hidEmployeeID)
              ->first();

          if (!empty($assignedprograms)){
            throw new Exception('Program is already assigned to employee');
          }

          $data = [
            'UserName' => $request->hidEmployeeID,
            'CourseID' => $request->newprogram,
            'DateSet' => date('Y-m-d'),
            'AddedBy' => auth()->user()->UserName
          ];

          $assignedprograms = DB::connection(strtolower($campus))
              ->table("accountcourse")
              ->insert($data);

          if ($assignedprograms){
            $user = DB::connection(strtolower($campus))
            ->table("employees")
            ->where('id', $request->hidEmployeeID)
            ->first();

            if (!empty($user->Cellphone)){

              $course = DB::connection(strtolower($campus))
                ->table("course")
                ->where('id', $request->newprogram)
                ->first();
              $sms = new SMSController();
              $sms->send($user->Cellphone,'Hi '.$user->FirstName.'! Your account, with departmental privileges, has been assigned to the '.$course->accro.' program for enrollment.');
            }
          }

        }catch(Exception $e){
          return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
      }
    }

    public function importhrmis(){
      $pageTitle = "Import From HRMIS";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.super.importhrmis',[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
      ]);
    }

    public function proimporthrmis(Request $request){
      try{
          $request->validate([
            'Campus' => 'required'
          ]);

          if (empty($request->Campus)){
            throw new Exception("Empty Campus");
          }

          $alls = DB::connection(strtolower($request->Campus))
            ->table('accountsuser')
            ->select('HRMISID')
            ->where('HRMISID', '>', 0)
            ->get();

          $hrmiss = DB::connection('hrmis')
            ->table('employee as e')
            ->select('e.*','csc.ItemName')
            ->leftjoin('cscitemname as csc', 'e.CurrentItem', '=', 'csc.id')
            ->whereIn('e.id', $alls->pluck('HRMISID')->toArray())
            ->where('e.Campus', GENERAL::Campuses()[$request->Campus]['ID'])
            ->whereNull('e.deleted_at')
            ->orderby('LastName')
            ->orderby('FirstName')
            ->get();
          $msg = "";
          foreach($hrmiss as $hrmis){
            $one = DB::connection(strtolower($request->Campus))
              ->table('accountsuser')
              ->where('HRMISID', $hrmis->id)
              ->first();

            $up = DB::connection(strtolower($request->Campus))
                ->table('employees')
                ->where('id', $one->Emp_No)
                ->update([
                  'EmploymentStatus' => $hrmis->EmploymentStatus,
                  'Cellphone' => $hrmis->Cellphone,
                  'CurrentItem' => $hrmis->ItemName,
                  'EmailAddress' => $hrmis->EmailAddress
                ]);

            if ($up){
              $msg .= '<li class = "text-success">'.$hrmis->LastName.', '.$hrmis->FirstName."'s Employment Status was changed to ".$hrmis->EmploymentStatus.
              ', Contact Number to '.$hrmis->Cellphone.
              ', Email Address to '.$hrmis->EmailAddress.
              ', Item to '.$hrmis->ItemName.'</li>';
            }else{
              $msg .= '<li class = "text-danger">'.$hrmis->LastName.', '.$hrmis->FirstName."'s Employment Status was not changed to ".$hrmis->EmploymentStatus.
              ', Contact Number to '.$hrmis->Cellphone.
              ', Email Address to '.$hrmis->EmailAddress.
              ', Item to '.$hrmis->ItemName.'</li>';
            }

          }
          return '<ul>'.$msg.'</ul>';
      }catch(Exception $e){
        return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
      }
    }
}
