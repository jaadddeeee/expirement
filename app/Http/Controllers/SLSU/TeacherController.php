<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SLSU\FacultyEvaluationController;
use Session;
use Exception;
use ROLE;
use GENERAL;
use Crypt;

use App\Models\DayList;
use App\Models\Prospectos;
use App\Models\CourseOffering;
use App\Models\Enrolled;
use App\Models\Student;
use App\Models\FacultyEvaluationSchedule;

class TeacherController extends Controller
{
  public function index()
  {
    $pageTitle = "My Class";
    $headerAction = '';

    $sy = 0;
    $sem = 0;
    if (!empty(session('schoolyear'))){
        $sy = session('schoolyear');
        $sem = session('semester');
    }

    return view('slsu.teacher.myclass',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction,
      'clicked' => 'class',
      'passsy' => $sy,
      'passsem' => $sem,
      'ViewType' => 0
    ]);

  }

  public function grades()
  {

    $pageTitle = "Encode Grades";
    $headerAction = '';

    $lists = [];

    $sy = 0;
    $sem = 0;

    $error = "";
    if (!empty(session('schoolyear'))){
      try{
        $sy = session('schoolyear');
        $sem = session('semester');
        $lists = CourseOffering::where('teacher', Auth::user()->Emp_No)
        ->withCount("enrolled")
        ->get();
      }catch(Exception $e){
        $error = "No record found";
      }

    }

    return view('slsu.teacher.mygrade', compact('lists'), [
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction,
      'clicked' => 'grades',
      'passsy' => $sy,
      'passsem' => $sem,
      'Error' => $error
    ]);

  }

  public function view(Request $request){

      $sy = $request->SchoolYear;
      $sem= $request->Semester;
      $ViewType = $request->ViewType;

      if (empty($sy)){
        return GENERAL::Error("Empty School Year");
      }

      if (empty($sem)){
        return GENERAL::Error("Empty Semester");
      }

      if (empty($ViewType)){
        return GENERAL::Error("Please select view type");
      }

      session([
        'schoolyear' => $sy,
        'semester' => $sem
      ]);

      try{

        $lists = CourseOffering::where('teacher', Auth::user()->Emp_No)
        ->orderby("sched")
        ->withCount("enrolled")
        ->get();

        $days = ['mon','tue','wed','thu','fri','sat','sun'];
        $times = ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30',
                  '13:00', '13:30', '14:00', '14:30','15:00','15:30', '16:00', '16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30','21:00'];
        $allDays = [];
        $blankSched = [];
        $daysWeek = [];
        if ($ViewType == 3){

          foreach($lists as $list){
            if (!empty($list->schedule)){
              if (!in_array($list->schedule->Diy, $daysWeek))
                $daysWeek[] = $list->schedule->Diy;
            }
          }

          foreach($lists as $list){
            if (empty($list->schedule)){
              array_push($blankSched,[
                'Subject' => $list->subject->courseno,
                'Title' => $list->subject->coursetitle,
                'CourseCode' => $list->coursecode,
                'Size' => $list->enrolled_count,
                'Units' => $list->subject->units,
                'ID' => $list->id
              ]);
            }else{
              array_push($allDays,[
                'day' => $list->schedule->Diy,
                'TimeInt' => strtotime($list->schedule->DateBegin),
                'TimeEndInt' => strtotime($list->schedule->DateEnd),
                'Time' => $list->schedule->DateBegin. ' ' .$list->schedule->DateEnd,
                'ID' => $list->id,
                'Room' => $list->schedule->Room
              ]);
            }
          }

          sort($daysWeek);

        }else{
          foreach($lists as $list){
            // dd($list);
              if (empty($list->schedule)){
                array_push($blankSched,[
                  'Subject' => $list->subject->courseno,
                  'Title' => $list->subject->coursetitle,
                  'CourseCode' => $list->coursecode,
                  'Size' => $list->enrolled_count,
                  'Units' => $list->subject->units,
                  'ID' => $list->id
                ]);
              }else{
                $sched = $list->schedule->Diy;
                $daytmp = DayList::where("DayOfWeek", $sched)->first();
                $daysArray = explode(",", $daytmp->FullName);

                foreach($daysArray as $dAr){
                  foreach($days as $day){
                    if (strtolower($day) == strtolower($dAr)){
                        array_push($allDays,[
                          'day' => $day,
                          'TimeInt' => strtotime($list->schedule->DateBegin),
                          'Time' => $list->schedule->DateBegin. ' ' .$list->schedule->DateEnd,
                          'TimeEndInt' => strtotime($list->schedule->DateEnd),
                          'ID' => $list->id,
                          'Room' => $list->schedule->Room,
                          'Subject' => $list->subject->courseno,
                          'CourseCode' => $list->coursecode
                        ]);

                    }
                  }
                }
              }
          }
        }



      }catch(Exception $e){
        return GENERAL::Error($e->getFile().": Line ".$e->getLine().": ".$e->getMessage());
      }
      sort($allDays);

      $path = 'myclasses';
      if ($ViewType == 1){
        $path = 'myclasses';
      }elseif ($ViewType ==2){
        $path = 'myclassesv_standard';
      }elseif ($ViewType ==3){
        $path = 'myclassesv_combo';
      }elseif ($ViewType ==4){
        $path = 'myclassesv_block';
      }
      return view('slsu.teacher.'.$path,compact('lists','days','allDays','daysWeek','times','blankSched'),[
        'clicked' => 'class',
        'passsy' => $sy,
        'passsem' => $sem,
        'ViewType' => $ViewType
      ]);
  }

  public function viewgrades(Request $request){

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

    return view('slsu.teacher.myclassesv_grades',compact('lists'),[
      'clicked' => 'grades',
      'passsy' => $sy,
      'passsem' => $sem
    ]);

}

  public function students(Request $request){

      $id = Crypt::decryptstring($request->sched);

      $pageTitle = "My Class";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $cc = "courseoffering".session('schoolyear').session("semester");

      $schedinfo = DB::connection(strtolower(session('campus')))
        ->table($cc)
        ->where('id', $id)
        ->first();

      $subinfo = Prospectos::find($schedinfo->courseid);

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
        ->orderBy("s.FirstName")
        ->get();

      // dd($lists);
      // dd($lists->enrolled[0]->registration->student->LastName);
      return view('slsu.teacher.listofstudents',compact('lists','subinfo'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'ID' => $id
      ]);

  }

  public function listgrades(Request $request){

    $id = Crypt::decryptstring($request->sched);
    $sy = Crypt::decryptstring($request->sy);
    $sem = Crypt::decryptstring($request->sem);
    $pageTitle = "Encode Grades";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    $cc = "courseoffering".session('schoolyear').session("semester");

    $schedinfo = DB::connection(strtolower(session('campus')))
      ->table($cc)
      ->where('id', $id)
      ->first();

    $subinfo = Prospectos::find($schedinfo->courseid);

    $enrolled = new Enrolled();

    $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
      'r.StudentYear','r.Section','r.finalize','s.StudentNo','s.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
      'sc1.tym as Time1', 'sc2.tym as Time2')
      ->where("courseofferingid", $id)
      ->where("r.SchoolYear", session('schoolyear'))
      ->where("r.Semester", session("semester"))
      ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
      ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
      ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
      ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
      ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
      ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
      ->orderBy("s.LastName")
      ->orderBy("s.FirstName")
      ->get();
      // dd($lists);
    return view('slsu.teacher.listofgrades',compact('lists','subinfo'),[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction,
      'ID' => $id,
      'passsy' => $sy,
      'passsem' => $sem
    ]);

}

  public function onestudent(Request $request){
    try{

      if (empty($request->id))
        throw new Exception("Invalid Student Number");

      $id = Crypt::decryptstring($request->id);

      $one = Student::where("StudentNo", $id)->first();

      return view("slsu.teacher.onestudent", compact('one'));

    }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
    }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
    }
  }

  public function unencoded(Request $request){


      $sy = $request->SchoolYear;
      $sem = $request->Semester;
      $pageTitle = "Unencoded Masterlist";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $cc = "courseoffering".$sy.$sem;
      $g = "grades".$sy.$sem;

      $lists = DB::connection(strtolower(session('campus')))
          ->table("registration as r")
          ->select("s.StudentNo","s.FirstName", "s.LastName", "s.MiddleName", "t.courseno", "t.coursetitle",
                  "cc.coursecode","cc.id")
          ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
          ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
          ->leftjoin("transcript as t", "g.sched", "=", "t.id")
          ->leftjoin("students as s", "g.StudentNo", "=", "s.StudentNo")
          ->where("r.SchoolYear", $sy)
          ->where("r.Semester", $sem)
          ->where("r.finalize", 1)
          ->where("cc.teacher", auth()->user()->Emp_No)
          ->where("g.final", "<=", 0)
          ->orderby("cc.coursecode")
          ->orderby("s.LastName")
          ->orderby("s.FirstName")
          ->get();

      return view('slsu.teacher.listofunencoded',compact('lists'),[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'passsy' => $sy,
            'passsem' => $sem
          ]);
  }

  public function encoded(Request $request){


    $sy = $request->SchoolYear;
    $sem = $request->Semester;
    $pageTitle = "Encoded Masterlist";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    $cc = "courseoffering".$sy.$sem;
    $g = "grades".$sy.$sem;

    $lists = DB::connection(strtolower(session('campus')))
        ->table("registration as r")
        ->select("s.StudentNo","s.FirstName", "s.LastName", "s.MiddleName", "t.courseno", "t.coursetitle",
                "cc.coursecode","cc.id")
        ->leftjoin($g." as g", "r.RegistrationID", "=", "g.gradesid")
        ->leftjoin($cc. " as cc", "g.courseofferingid", "=", "cc.id")
        ->leftjoin("transcript as t", "g.sched", "=", "t.id")
        ->leftjoin("students as s", "g.StudentNo", "=", "s.StudentNo")
        ->where("r.SchoolYear", $sy)
        ->where("r.Semester", $sem)
        ->where("r.finalize", 1)
        ->where("cc.teacher", auth()->user()->Emp_No)
        ->where("g.final", ">", 0)
        ->orderby("cc.coursecode")
        ->orderby("s.LastName")
        ->orderby("s.FirstName")
        ->get();

    return view('slsu.teacher.listofencoded',compact('lists'),[
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'passsy' => $sy,
          'passsem' => $sem
        ]);
  }

  public function facultyevaluation(){
    try{

      $afes = new FacultyEvaluationController();

      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
      ->orderby("Semester")
      ->get();

      $perSchedAVGs = [];
      foreach($scheds as $sched){
          $ress = $afes->getRatingOneFaculty([
            'SchoolYear' => $sched->SchoolYear,
            'Semester' => $sched->Semester,
            'EmployeeID' => auth()->user()->Emp_No,
            'Campus' => session('campus')
          ]);


          $cat = [];
          $fivecat = [];
          $avgfivecat = 0;
          $overallavg = 0;
          if (count($ress) > 0){
            for($x=1;$x<=4;$x++){
              $avg = 0;
              $five = 0;
              $ctr=0;
              foreach($ress as $res){
                if ($x == $res->GroupID){
                  $avg += $res->sumRating;
                  $five += $res->sumRating / 5;
                  $ctr++;
                }
              }

              $tmpC = $avg / $ctr;
              $tmpAv = $five / $ctr;
              $avgfivecat +=  ($tmpAv > 5 ? 5 : $tmpAv);
              $cat[] = ($tmpC > 25 ? 25 : $tmpC);
              $fivecat[] = ($tmpAv > 5 ? 5 : $tmpAv);
              $overallavg += ($avg / $ctr);

              // $avgfivecat += ($five / $ctr) ;
              // $cat[] = $avg / $ctr;
              // $fivecat[] = $five / $ctr;
              // $overallavg += ($avg / $ctr);
            }
          }

          $perSchedAVGs[] = [
            'Category' => $cat,
            'FiveCat' => $fivecat,
            'AVGFiveCat' => $avgfivecat / 4,
            'Average' => $overallavg,
            'SchoolYear' => $sched->SchoolYear,
            'Semester' => $sched->Semester,
          ];

      }

      $pageTitle = "FACULTY PERFORMANCE STUDENT EVALUATION";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
      return view('slsu.facultyevaluation.oneteacher', compact('perSchedAVGs'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
      ]);
    }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
    }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
    }
  }
}
