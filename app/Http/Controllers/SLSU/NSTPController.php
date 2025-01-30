<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Student;
use App\Models\Registration;
use App\Models\TMPNSTP;

use Crypt;
use Exception;
use GENERAL;
use Schema;

class NSTPController extends Controller
{
  public function withserial()
  {

    $pageTitle = "NSTP with Serial Number";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    $students = Student::whereNot("NSTPSerial", "")
      ->where("notuse", 0)
      ->orderby("LastName")->orderby("FirstName")
      ->paginate(500);

    return view('slsu.nstp.withserial', compact('students'), [
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
    ]);

  }

  public function withnoserial()
  {
      $pageTitle = "NSTP with No Serial Number";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.nstp.withnoserial',[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);

  }

  public function masterlist(){

    $pageTitle = "NSTP - Officially Enrolled";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.nstp.masterlist',[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
  }

  public function masterlistpro(Request $request){
      try{

        $sy = $request->SchoolYear;
        $sem = $request->Semester;
        $cluster = Crypt::decryptstring($request->NSTP);
        $request->validate([
          'SchoolYear' => 'required',
          'Semester' => 'required',
          'NSTP' => 'required',
        ]);

        $table = "grades".$sy.$sem;

        $ress = DB::connection(strtolower(session('campus')))
          ->table('registration as r')
          ->select('s.LastName','s.FirstName','s.MiddleName','c.accro',
            'm.course_major','s.Sex','s.p_province','s.p_municipality','s.p_street','s.ContactNo','s.email')
          ->leftjoin($table.' as g', 'r.RegistrationID', '=', 'g.gradesid')
          ->leftjoin('students as s', 'r.StudentNo', '=', 's.StudentNo')
          ->leftjoin('transcript as t', 'g.sched', '=', 't.id')
          ->leftjoin('course as c', 'r.Course', '=','c.id')
          ->leftjoin('major as m', 'r.Major', '=','m.id')
          ->where("r.finalize", 1)
          ->where('r.SchoolYear', $sy)
          ->where("r.Semester", $sem)
          ->where("t.proceed", $cluster)
          ->where("t.exempt", 1)
          ->orderBy("s.LastName")
          ->orderBy("s.FirstName")
          ->get();

        return view('slsu.nstp.listofficial', compact('ress'));
      }catch(Exception $e){
          return response()->json(['errors' => $e->getMessage()],400);
      }
  }

  public function searchnoserial(Request $request){

    try{
      $code = [];
      $sy = (isset($request->SchoolYear)?$request->SchoolYear:"");
      $cluster = (isset($request->NSTP)?\Crypt::decryptstring($request->NSTP):"");

      if (strtolower(session('campus')) == "hn"){
          if ($cluster == "424-2B"){
              $code[] = "2040101000a2";
              $code[] = "424-2B";
          }else{
              $code[] = "2040101000a1";
              $code[] = "424-2A";
          }
      }else{
        $code[] = $cluster;
      }

      // dd($code);
      // dd(strtolower(session('campus')));
    }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
    }

    if (empty($sy))
        return GENERAL::Error("Invalid School Year");

    if (empty($cluster))
        return GENERAL::Error("Invalid Cluster");

    $students = "";

    $sems = [1,2];

    TMPNSTP::truncate();

    foreach($sems as $sem){
        set_time_limit(0);
        $g = "grades".$sy.$sem;
        if (Schema::connection(strtolower(session('campus')))->hasTable($g)){
          // dd($g);
          $registrations = DB::connection(strtolower(session('campus')))->table($g." as g")
            ->select("registration.StudentNo",
                  "RegistrationID", "registration.SchoolYear", "registration.Semester", "g.final",
                  't.courseno')
            ->leftjoin("registration", "g.gradesid", "=", "registration.RegistrationID")
            ->leftjoin("students as s", "g.StudentNo", "=", "s.StudentNo")
            ->leftjoin("transcript as t", "g.sched", "=", "t.id")
            ->where("finalize", 1)
            ->where("SchoolLevel", "Under Graduate")
            ->where("registration.SchoolYear", $sy)
            ->where("registration.Semester", $sem)
            ->where("t.exempt", 1)
            ->whereIn("t.proceed", $code)
            ->where("s.NSTPSerial", "")
            ->orderby("registration.SchoolYear")
            ->get();

          // dd($registrations);

          foreach($registrations as $reg){
                $data = [
                    'StudentNo' => $reg->StudentNo,
                    'sy'.$sem => $reg->SchoolYear,
                    'sem'.$sem => $sem,
                    'grade'.$sem => $reg->final,
                    'subject'.$sem => $reg->courseno
                ];

                $exist = TMPNSTP::where("StudentNo", $reg->StudentNo)->first();
                if (!$exist){
                  TMPNSTP::create($data);
                }else{
                  TMPNSTP::where("StudentNo", $reg->StudentNo)
                    ->update($data);
                }

          }
        }
    }

    $nstp = new TMPNSTP();

    $tmps = TMPNSTP::select('s.FirstName', 's.MiddleName', 's.LastName','c.accro', 'm.course_major',
          'tmp_nstp.*','s.Sex','s.BirthDate','s.p_municipality', 's.p_province','s.ContactNo','s.email')
          ->leftjoin('students as s', 'tmp_nstp.StudentNo', "=", 's.StudentNo')
          ->leftjoin('course as c', 's.Course', '=', 'c.id')
          ->leftjoin('major as m', 's.major', '=', 'm.id')
          ->orderby("s.LastName")->orderby("s.FirstName")
          ->where(function($query){
              $query->orwhere('grade1', "<=", 3)
                ->orwhere('grade2', "<=", 3);
          })
          ->get();

    return view('slsu.nstp.listofnoserial', compact('tmps'));
  }

  public function grade(){
    $pageTitle = "Search NSTP Grades";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    return view('slsu.nstp.grades', [
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
    ]);
  }

  public function studentsearch(Request $request){

    $id = $request->id;
    $tmp = explode(" - ", $id);

    $Studentno = (isset($tmp[0])?$tmp[0]:"");

    if (empty($Studentno))
      return "<div class = 'alert alert-danger'>Invalid input!</div><br><br><br><br><br><br><br><br><br><br><br><br><br>";


    $Student = Student::where("StudentNo", $Studentno)
        ->where("notuse", 0)
        ->first();

    if (empty($Student)){
        return "<div class = 'alert alert-danger'><b>".$id."</b> not found!</div><br><br><br><br><br><br><br><br><br><br><br><br><br>";
    }

    $regs = Registration::where("StudentNo", $Studentno)
      ->where('finalize', 1)
      ->orderby("SchoolYear", "DESC")
      ->orderby("Semester", "DESC")
      ->get();

    if (empty($regs))
      return "<div class = 'alert alert-danger'>No record found for student <b>".$id."</b>!</div><br><br><br><br><br><br><br><br><br><br><br><br><br>";

    $data = [];
    foreach($regs as $reg){
        $g = "grades".$reg->SchoolYear.$reg->Semester;
        $grades = DB::connection(strtolower(session('campus')))
          ->table($g." as g")
          ->select('g.final','t.courseno')
          ->leftjoin("transcript as t", 'g.sched', '=', 't.id')
          ->where('g.gradesid', $reg->RegistrationID)
          ->where('t.exempt', 1)
          ->whereIn("proceed", ['424-2B','424-2A','2040101000a2','2040101000a1'])
          ->first();

        if (!empty($grades)){
            array_push($data,[
              'SchoolYear' => $reg->SchoolYear,
              "Semester" => $reg->Semester,
              'Grade' => $grades->final,
              'Subject' => $grades->courseno
            ]);
        }
    }

    return view('slsu.nstp.result-grade', compact('data'));
  }

}
