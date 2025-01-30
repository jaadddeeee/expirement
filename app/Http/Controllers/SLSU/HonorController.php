<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;


use App\Models\Role;
use App\Models\Course;


use Crypt;
use GENERAL;
use Exception;


class HonorController extends Controller
{
    protected $sy;
    protected $sem;

    public function __construct(){
      $pref = GENERAL::getStudentDefaultEnrolment();
      if (empty($pref))
        return GENERAL::Error("Student preference not set");

      $this->sy = $pref['SchoolYear'];
      $this->sem = $pref['Semester'];

    }

    public function latinhonors(){

      $role = Role::where("EmpID", Auth::user()->Emp_No)
              ->where("Role", "Department")
              ->first();

      $error = "";
      $courses = [];

      if (Auth::user()->AllowSuper){
        $courses = Course::where("lvl", "Under Graduate")
          ->where('isActive', 0)
          ->orderby("accro")
          ->get();
      }else{
        if (!empty($role)){
          if (!empty($role->DepartmentID)){
            $courses = Course::where("Department", $role->DepartmentID)
                ->get();
            }else{
              $error = "Invalid Department ID.";
            }
        }else{
          $error = "Invalid Role.";
        }
      }

      $pageTitle = "Tentative List - Latin Honors";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.recognition.latinhonors', compact('courses'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'Error' => $error
      ]);
    }

    public function deanslist(){

      $role = Role::where("EmpID", Auth::user()->Emp_No)
              ->where("Role", "Department")
              ->first();

      $error = "";
      $courses = [];

      if (Auth::user()->AllowSuper){
        $courses = Course::where("lvl", "Under Graduate")
          ->where('isActive', 0)
          ->orderby("accro")
          ->get();
      }else{
        if (!empty($role)){
          if (!empty($role->DepartmentID)){
            $courses = Course::where("Department", $role->DepartmentID)
                ->get();
            }else{
              $error = "Invalid Department ID.";
            }
        }else{
          $error = "Invalid Role.";
        }
      }

      $pageTitle = "Tentative List - Dean's Listers";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.recognition.deanslist', compact('courses'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'Error' => $error
      ]);
    }

    public function prolatinhonors(Request $request){

        $id = $request->Course;

        if (empty($id))
          return GENERAL::Error("Invalid Course");

        $res = DB::connection(strtolower(session('campus')))
          ->table("registration as r")
          ->leftjoin("students as s", "r.StudentNo", "=", "s.StudentNo")
          ->leftjoin("major as m", "r.Major", "=", "m.id")
          ->select("r.StudentNo", "s.FirstName", "s.LastName","m.course_major")
          ->where("r.Course", $id)
          ->where("r.SchoolYear", $this->sy)
          ->where("r.Semester", $this->sem)
          ->where("r.finalize", 1)
          ->where("r.StudentYear", 4)
          ->orderby("m.course_major", "ASC")
          ->orderby("s.LastName")
          ->orderby("s.FirstName")
          ->get();

        $all = [];
        foreach($res as $one){
            $runningTotal = 0;
            $runningUnit = 0;
            $EarnedUnits = 0;
            $status = "Continuing";
            $students = DB::connection(strtolower(session('campus')))
              ->table("registration as r")
              ->select("r.RegistrationID","SchoolYear","Semester", "StudentStatus")
              ->where("r.finalize", 1)
              ->where("r.StudentNo", $one->StudentNo)
              ->get();
            foreach($students as $student){
                if ($student->SchoolYear == $this->sy and $student->Semester == $this->sem){

                }else{
                  if ($student->StudentStatus == "Transferee")
                    $status = "Transferee";

                  if ($student->StudentStatus == "Shiftee")
                    $status = "Shiftee";

                  $tblGrade = "grades".$student->SchoolYear.$student->Semester;
                  $datas = DB::connection(strtolower(session('campus')))
                    ->table($tblGrade." as g")
                    ->select("t.units",  "g.*")
                    ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                    ->where("gradesid", $student->RegistrationID)
                    ->where("t.exempt", "<>", 1)
                    ->get();
                  foreach($datas as $d){
                      set_time_limit(0);
                      $out = GENERAL::ComputeForGWA($d->final, $d->inc, $d->units);
                      $runningTotal += $out['RunningTimes'];
                      $runningUnit += $out['RunningUnit'];
                      $EarnedUnits += $out['UnitsEarned'];
                  }
                }
            }

            $gwa2 = ($runningTotal + 9) / ($runningUnit +9);
            $gwa = ($runningTotal / $runningUnit);
            if ($gwa2 <= 1.65){
              array_push($all, [
                'GWA' => $gwa,
                'GWA2' => $gwa2,
                'FirstName' => $one->FirstName,
                'LastName' => $one->LastName,
                'Major' => $one->course_major,
                'StudentNo' => $one->StudentNo,
                "EarnedUnits" => $EarnedUnits,
                "Status" => $status

              ]);
            }

        }


        return view('slsu.recognition.gwa', compact('all'));
    }

    public function prodeanslist(Request $request){

      try{

        $id = $request->Course;
        $sy = $request->SchoolYear;
        $sem = $request->Semester;;

        if (empty($id))
          throw new Exception("Invalid Course");
        if (empty($sy))
          throw new Exception("Invalid School Year");
        if (empty($sem))
          throw new Exception("Invalid Semester");

        $res = DB::connection(strtolower(session('campus')))
          ->table("registration as r")
          ->leftjoin("students as s", "r.StudentNo", "=", "s.StudentNo")
          ->leftjoin("major as m", "r.Major", "=", "m.id")
          ->select("r.*", "s.FirstName", "s.LastName","m.course_major")
          ->where("r.Course", $id)
          ->where("r.SchoolYear", $sy)
          ->where("r.Semester", $sem)
          ->where("r.finalize", 1)
          ->orderby("m.course_major", "ASC")
          ->orderby("r.StudentYear")
          ->orderby("s.LastName")
          ->orderby("s.FirstName")
          ->get();

        $all = [];

        foreach($res as $student){

            $runningTotal = 0;
            $runningUnit = 0;
            $EarnedUnits = 0;
            $status = "Continuing";

            if ($student->StudentStatus == "Transferee")
              $status = "Transferee";

            if ($student->StudentStatus == "Shiftee")
              $status = "Shiftee";

            $tblGrade = "grades".$student->SchoolYear.$student->Semester;
            $datas = DB::connection(strtolower(session('campus')))
              ->table($tblGrade." as g")
              ->select("t.units",  "g.*")
              ->leftjoin("transcript as t", "g.sched", "=", "t.id")
              ->where("gradesid", $student->RegistrationID)
              ->where("t.exempt", "<>", 1)
              ->get();

            foreach($datas as $d){
                set_time_limit(0);
                $out = GENERAL::ComputeForGWA($d->final, $d->inc, $d->units);
                // dd($out);
                $runningTotal += $out['RunningTimes'];
                $runningUnit += $out['RunningUnit'];
                $EarnedUnits += $out['UnitsEarned'];
            }
            if (!empty($runningUnit)){
            $gwa = ($runningTotal / $runningUnit);
              if ($gwa <= 1.65){
                array_push($all, [
                  'GWA' => $gwa,
                  'FirstName' => $student->FirstName,
                  'LastName' => $student->LastName,
                  'Major' => $student->course_major,
                  'StudentNo' => $student->StudentNo,
                  'YearLevel' => $student->StudentYear,
                  "EarnedUnits" => $EarnedUnits,
                  "Status" => $status
                ]);
              }
            }
        }

        if (empty($all))
          throw new Exception("No potential Dean's List candidates found.");

        return view('slsu.recognition.gwa-dean', compact('all'));
      }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
      }

  }
}
