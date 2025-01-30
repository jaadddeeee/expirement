<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Exception;
use ROLE;
use GENERAL;
use App\Models\Registration;
use App\Models\Course;
class HomeController extends Controller
{
  protected $pref;
  public function __construct(){
      $this->objSched = new ScheduleController();
      $this->pref = GENERAL::getStudentDefaultEnrolment();
      if (empty($this->pref))
        return GENERAL::Error("Student preference not set");
  }

  public function index()
  {


      $sy = (!empty(session('schoolyear'))?session('schoolyear'):date('Y'));
      $sem = (!empty(session('semester'))?session('semester'):1);
      session([
        'schoolyear' => $sy,
        'semester' => $sem
      ]);

      $empID = auth()->user()->Emp_No;

      $countGrade = [];
      $countNoGrade = [];
      $countCC = 0;
      $countAccepted = 0;

      if (ROLE::isTeacher()){
        $cc = "courseoffering".$sy.$sem;
        $g = "grades".$sy.$sem;

        try{
          $countGrade = DB::connection(strtolower(session('campus')))
                    ->table("registration as r")
                    ->select(DB::connection(strtolower(session('campus')))->raw("count(g.final) as cGrade"))
                    ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
                    ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
                    ->where("r.SchoolYear", $sy)
                    ->where("r.Semester", $sem)
                    ->where("r.finalize", 1)
                    ->where("cc.teacher", $empID)
                    ->where("g.final", ">", 0)
                    ->first();

          $countNoGrade = DB::connection(strtolower(session('campus')))
                    ->table("registration as r")
                    ->select(DB::connection(strtolower(session('campus')))->raw("count(g.final) as cGrade"))
                    ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
                    ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
                    ->where("r.SchoolYear", $sy)
                    ->where("r.Semester", $sem)
                    ->where("r.finalize", 1)
                    ->where("cc.teacher", $empID)
                    ->where("g.final", "<=", 0)
                    ->first();
        }catch(Exception $e){

        }

      }

      if (ROLE::isRegistrar()){
        $cc = "courseoffering".$sy.$sem;
        if (Schema::connection(strtolower(session('campus')))->hasTable($cc)){
          $countCC = DB::connection(strtolower(session('campus')))
              ->table($cc." as cc")
              ->where("teacher", ">", 0)
              ->get()->count();

          $countAccepted = DB::connection(strtolower(session('campus')))
          ->table("trackgradesheet")
          ->whereNotNull("AcceptedBy")
          ->where("SchoolYear", $sy)
          ->where("Semester", $sem)
          ->get()->count();
        }

      }

      $enrolmentstatuss = [];
      $courses = [];

      if (ROLE::isDepartment()){
        $courses = DB::connection(strtolower(session('campus')))
        ->table("accountcourse as ac")
        ->leftjoin('course as c', 'ac.CourseID', '=', 'c.id')
        ->select("c.id", 'c.accro')
        ->where("ac.UserName", strtolower(auth()->user()->Emp_No))
        ->get();
      }

      if (ROLE::isRegistrar() or auth()->user()->AllowSuper == 1 or strtolower(auth()->user()->AccountLevel) == "administrator"){
        $courses = Course::orderby('accro')
            ->where('isActive', "Yes")
            ->get();
      }

      if (ROLE::isRegistrar() or  ROLE::isDepartment() or auth()->user()->AllowSuper == 1 or strtolower(auth()->user()->AccountLevel) == "administrator"){
        $statsTMP = Registration::query();
        $statsTMP->select('Course', 'finalize', 'SchoolLevel','TES',
            DB::connection(strtolower(session('campus')))->raw('COUNT(*) as countEncoded'));
        $statsTMP->where('SchoolYear', $this->pref['SchoolYear'])
        ->where('Semester', $this->pref['Semester']);

        if (ROLE::isRegistrar() or auth()->user()->AllowSuper == 1 or strtolower(auth()->user()->AccountLevel) == "administrator"){
        }else{
            $assignedprograms = DB::connection(strtolower(session('campus')))
              ->table("accountcourse")
              ->select("CourseID")
              ->where("UserName", strtolower(auth()->user()->Emp_No))
              ->pluck("CourseID")->toArray();

            $statsTMP->whereIn("registration.Course", $assignedprograms);
        }
        $statsTMP->groupby('Course')
        ->groupby('finalize')
        ->groupby('SchoolLevel')
        ->groupby('TES');
        $enrolmentstatuss = $statsTMP->get();

      }

      // if (auth()->user()->AllowSuper == 1){
      //   dd($enrolmentstatuss);
      // }
      return view('content.dashboard.index', compact('countGrade','countNoGrade','countCC','countAccepted','enrolmentstatuss','courses'),[
        'SchoolYear' => $this->pref['SchoolYear'],
        'Semester' => $this->pref['Semester']
      ]);
    // return view('content.dashboard.dashboards-analytics');
  }

  public function sem_year_gs(Request $request){
    $sy = (!empty($request->SchoolYear)?$request->SchoolYear:date('Y'));
    $sem = (!empty($request->Semester)?$request->Semester:1);
    $cc = "courseoffering".$sy.$sem;

    session([
      'schoolyear' => $sy,
      'semester' => $sem
    ]);

    try{
      $countCC = 0;
      $countAccepted = 0;

      $cc = "courseoffering".$sy.$sem;
      if (Schema::connection(strtolower(session('campus')))->hasTable($cc)){
        $countCC = DB::connection(strtolower(session('campus')))
            ->table($cc." as cc")
            ->where("teacher", ">", 0)
            ->get()->count();

        $countAccepted = DB::connection(strtolower(session('campus')))
        ->table("trackgradesheet")
        ->whereNotNull("AcceptedBy")
        ->where("SchoolYear", $sy)
        ->where("Semester", $sem)
        ->get()->count();
      }
      $perAccepted = ($countCC == 0?0:($countAccepted / $countCC) * 100);
      $perNoSubmission = ($countCC==0?0:(($countCC-$countAccepted) / $countCC) * 100);
      // dd($cc);
      return response()->json([
          'gsTotalSc' => number_format($countCC,0,'',','),
          'gsAccepted' => number_format($countAccepted,0,'',','),
          'gsNoSubmission' => number_format($countCC-$countAccepted,0,'',','),
          'perAccepted' => "<i class='bx bx-up-arrow-alt'></i> ".number_format($perAccepted,2,'.','')."%",
          'perNoSubmission' => "<i class='bx bx-down-arrow-alt'></i> ".number_format($perNoSubmission,2,'.','')."%"
        ]);
    }catch(\Exception $e){
      return response()->json([
        'gsTotalSc' => "Error",
        'gsAccepted' => "Error",
        'gsNoSubmission' => 0,
        'perAccepted' => "<i class='bx bx-up-arrow-alt'></i> 0%",
        'perNoSubmission' => "<i class='bx bx-down-arrow-alt'></i> 0  %"
      ]);
    }
  }

  public function sem_year_analytics(Request $request)
  {
      $sy = (!empty($request->SchoolYear)?$request->SchoolYear:date('Y'));
      $sem = (!empty($request->Semester)?$request->Semester:1);


      $empID = auth()->user()->Emp_No;

      $cc = "courseoffering".$sy.$sem;
      $g = "grades".$sy.$sem;

      try{


        $countGrade = DB::connection(strtolower(session('campus')))
                      ->table("registration as r")
                      ->select(DB::connection(strtolower(session('campus')))->raw("count(g.final) as cGrade"))
                      ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
                      ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
                      ->where("r.SchoolYear", $sy)
                      ->where("r.Semester", $sem)
                      ->where("r.finalize", 1)
                      ->where("cc.teacher", $empID)
                      ->where("g.final", ">", 0)
                      ->first();

        $countNoGrade = DB::connection(strtolower(session('campus')))
                      ->table("registration as r")
                      ->select(DB::connection(strtolower(session('campus')))->raw("count(g.final) as cGrade"))
                      ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
                      ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
                      ->where("r.SchoolYear", $sy)
                      ->where("r.Semester", $sem)
                      ->where("r.finalize", 1)
                      ->where("cc.teacher", $empID)
                      ->where("g.final", "<=", 0)
                      ->first();
        $gGrowth = 0;
        $cGrade = (isset($countGrade->cGrade)?$countGrade->cGrade:0);
        $cNoGrade = (isset($countNoGrade->cGrade)?$countNoGrade->cGrade:0);
        $total = $cGrade + $cNoGrade;
        if (!empty($total) and !empty($cGrade)){
          $gGrowth = ($cGrade / $total) * 100;

          if ($gGrowth > 0 and $gGrowth <= 1){
            $gGrowth = 1;
          }else{
            $gGrowth = round($gGrowth);
          }
        }
        return response()->json([
            'cGrade' => '<a class = "text-dark" href = "'.route('view.encoded',['SchoolYear' => $sy,'Semester'=>$sem]).'">'.$cGrade.' students</a>',
            'cNoGrade' => '<a class = "text-dark" href = "'.route('view.unencoded',['SchoolYear' => $sy,'Semester'=>$sem]).'">'.$cNoGrade.' students</a>',
            'total' => $total,
            'gGrowth' => $gGrowth,
            'nGrowth' => (100-$gGrowth),
          ]);
      }catch(\Exception $e){
        return response()->json([
          'cGrade' => "Error",
          'cNoGrade' => "Error",
          'total' => 0,
          'gGrowth' => 0,
          'nGrowth' => "Error"
        ]);
      }

      // return view('content.dashboard.index', compact('countGrade','countNoGrade'),[
      //   'SchoolYear' => $sy,
      //   'Semester' => $sem
      // ]);
    // return view('content.dashboard.dashboards-analytics');
  }
}
