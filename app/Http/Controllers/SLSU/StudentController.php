<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Exception;
use ROLE;
use GENERAL;
use Crypt;

use App\Models\DayList;

use App\Models\CourseOffering;
use App\Models\Enrolled;

// wapa na edit ni
class StudentController extends Controller
{
  public function index()
  {
    $pageTitle = "My Class";
    $headerAction = '';
    return view('slsu.teacher.myclass',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
    ]);

  }

  public function view(Request $request){

      $sy = $request->SchoolYear;
      $sem= $request->Semester;

      if (empty($sy)){
        return GENERAL::Error("Empty School Year");
      }

      if (empty($sem)){
        return GENERAL::Error("Empty Semester");
      }


      session([
        'schoolyear' => $sy,
        'semester' => $sem
      ]);

      try{
        $lists = CourseOffering::where('teacher', Auth::user()->Emp_No)
        ->withCount("enrolled")
        ->get();
      }catch(Exception $e){
        return GENERAL::Error("No record found");
      }

      return view('slsu.teacher.myclasses',compact('lists'));
  }

  public function students(Request $request){

      $id = Crypt::decryptstring($request->sched);

      $pageTitle = "My Class";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $cc = "courseoffering".session('schoolyear').session("semester");
      $enrolled = new Enrolled();
      // dd();
      $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
        'c.accro','r.StudentYear','r.Section','r.finalize','s.StudentNo','s.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
        'sc1.tym as Time1', 'sc2.tym as Time2')
        ->where("courseofferingid", $id)
        ->where("r.SchoolYear", session('schoolyear'))
        ->where("r.Semester", session("semester"))
        ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
        ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
        ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
        ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
        ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
        ->leftjoin("course as c", "r.Course", "=", "c.id")
        ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
        ->orderBy("s.LastName")
        ->get();

      // dd($lists);
      // dd($lists->enrolled[0]->registration->student->LastName);
      return view('slsu.teacher.listofstudents',compact('lists'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'ID' => $id
      ]);

  }

  public function onestudent(Request $request){
    try{

        $studentnumber = Crypt::decryptstring($request->id);
        $campus = session('campus');

        $one = DB::connection(strtolower($campus))
            ->table('students as s')
            ->select("s.*", "c.accro", "m.course_major")
            ->leftjoin("course as c", "s.Course", "=", "c.id")
            ->leftjoin("major as m", "s.major", "m.id")
            ->where("s.StudentNo", $studentnumber)
            ->first();

        $onesub = DB::connection(strtolower($campus))
            ->table('students2 as s')
            ->where("s.StudentNo", $studentnumber)
            ->first();

        $pageTitle = utf8_decode(strtoupper($one->FirstName . (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') .$one->LastName));
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.student.one',compact('one','onesub'), [
              'pageTitle' => $pageTitle,
              'headerAction' => $headerAction,
              'str' => $request->str,
              'campus' => $campus
        ]);

    }catch(DecryptException $e){
        return "Invalid hash.";
    }
  }

}
