<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;
use GENERAL;
use Crypt;
use ROLE;

use App\Models\FacultyEvaluationSchedule;
use App\Models\FacultyEvaluationResult;
use App\Models\FacultyEvaluationForm;
use App\Models\FacultyEvaluationFeedback;
use App\Models\Department;
use App\Models\Employee;

class FacultyEvaluationController extends Controller
{
    //
    // Display survey creation form

    public function index(){

      $pageTitle = "Faculty Evaluation Schedules";
      $headerAction = '';

      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
        ->orderby("Semester")
        ->get();

      return view('slsu.facultyevaluation.index',compact('scheds'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function one(Request $request){

      try{

        $id = Crypt::decryptstring($request->id);

        $sched = FacultyEvaluationSchedule::where("id",$id)
          ->first();
        if (empty($sched))
          throw new Exception("Schedule not found.");

        $cc = "courseoffering".$sched->SchoolYear.$sched->Semester;
        $rating = "facultyevaluationresult".$sched->SchoolYear.$sched->Semester;

        if (!Schema::connection(strtolower(session('campus')))->hasTable($cc)) {
          throw new Exception("Table CC not found.");
        }

        if (!Schema::connection('evaluation')->hasTable($rating)) {
          throw new Exception("Table facultyevaluationresult not found.");
        }

        $results = DB::connection('evaluation')->table($rating)
            ->select("EmployeeID","QuestionID", "Rating",DB::connection('evaluation')->raw('count(*) as cRating'))
            ->where("Campus",session('campus'))
            ->groupby('EmployeeID')
            ->groupby('QuestionID')
            ->groupby("Rating")
            ->get();

        $forms = FacultyEvaluationForm::orderby("GroupID")
            ->orderby("Sequence")
            ->get();

        $faculties = DB::connection(strtolower(session('campus')))
            ->table($cc.' as cc')
            ->select("e.LastName",'e.FirstName','e.id')
            ->leftjoin('employees as e', 'cc.teacher', '=','e.id')
            ->where("teacher", ">", 0)
            ->groupby("e.id")
            ->groupby("e.LastName")
            ->groupby("e.FirstName")
            ->orderby("e.LastName")
            ->orderby("e.FirstName")
            ->get();
            // dd($faculties);
        $pageTitle = "Faculty Evaluation Result (".GENERAL::setSchoolYearLabel($sched->SchoolYear,$sched->Semester)." - ".GENERAL::Semesters()[$sched->Semester]['Short'].")";
        $headerAction = '';

        return view('slsu.facultyevaluation.one',compact('sched','faculties','forms','results'),[
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction
        ]);
      }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
      }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
      }


    }

    public function export(){

      $pageTitle = "Export Faculty Evaluation";
      $headerAction = '';

      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
        ->orderby("Semester")
        ->get();

      return view('slsu.facultyevaluation.export',compact('scheds'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function exportpdf(){

      $pageTitle = "Export to PDF Faculty Evaluation";
      $headerAction = '';

      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
        ->orderby("Semester")
        ->get();

      return view('slsu.facultyevaluation.exportpdf',compact('scheds'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function proexport(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);
        $sched = FacultyEvaluationSchedule::find($id);
        if (empty($sched)){
          throw new Exception('Invalid schedule.');
        }

        $cc = "courseoffering".$sched->SchoolYear.$sched->Semester;
        $sumTble = "facultyevaluationsummary".$sched->SchoolYear.$sched->Semester;

        $this->createTableSummary($sumTble);
        $this->createTableLastUpdate();
        DB::connection("evaluation")->table($sumTble)->truncate();
        foreach(GENERAL::Campuses() as $index => $campus){
          $perSchedAVGs = [];
          $lists = DB::connection(strtolower($index))
              ->table($cc.' as cc')
              ->select('cc.teacher', 'e.LastName', 'e.FirstName',
                'e.MiddleName', 'e.Department','d.DepartmentName','e.EmploymentStatus')
              ->leftjoin('employees as e', 'cc.teacher', '=', 'e.id')
              ->leftjoin('department as d', 'e.Department', '=', 'd.id')
              ->where('cc.teacher', '<>', 0)
              ->groupby('cc.teacher')
              ->groupby('e.LastName')
              ->groupby('e.FirstName')
              ->orderby('e.LastName')
              ->orderby('e.FirstName')
              ->get();

          foreach($lists as $list){
            set_time_limit(0);
            $ress = $this->getRatingOneFaculty([
              'SchoolYear' => $sched->SchoolYear,
              'Semester' => $sched->Semester,
              'EmployeeID' => $list->teacher,
              'Campus' => $index
            ]);

            $cat = [];
            $fivecat = [];
            $avgfivecat = 0;
            $overallavg = 0;
            $c1 = 0;
            $c2 = 0;
            $c3 = 0;
            $c4 = 0;
            $p1 = 0;
            $p2 = 0;
            $p3 = 0;
            $p4 = 0;

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

                if ($x == 1){
                  $c1 = $avg / $ctr;
                  $p1 = $five / $ctr;
                }

                if ($x == 2){
                  $c2 = $avg / $ctr;
                  $p2 = $five / $ctr;
                }

                if ($x == 3){
                  $c3 = $avg / $ctr;
                  $p3 = $five / $ctr;
                }

                if ($x == 4){
                  $c4 = $avg / $ctr;
                  $p4 = $five / $ctr;
                }

              }
            }

            $perSchedAVGs[] = [
              'EmployeeID' => $list->teacher,
              'DepartmentID' => $list->Department,
              'DepartmentName' => $list->DepartmentName,
              'EmploymentStatus' => $list->EmploymentStatus,
              'LastName' => $list->LastName,
              'FirstName' => $list->FirstName,
              'MiddleName' => $list->MiddleName,
              'SchoolYear' => $sched->SchoolYear,
              'Semester' => $sched->Semester,
              'Campus' => $index,
              'ScheduleID' => 0,
              'C1' => ($c1 > 25 ? 25 : $c1),
              'C2' => ($c2 > 25 ? 25 : $c2),
              'C3' => ($c3 > 25 ? 25 : $c3),
              'C4' => ($c4 > 25 ? 25 : $c4),
              'P1' => ($p1 > 5 ? 5 : $p1),
              'P2' => ($p2 > 5 ? 5 : $p2),
              'P3' => ($p3 > 5 ? 5 : $p3),
              'P4' => ($p4 > 5 ? 5 : $p4),
            ];


          }

          $save = DB::connection('evaluation')
          ->table($sumTble)
          ->insert($perSchedAVGs);

        }

        DB::connection('evaluation')->table('facultyevaluationupdate')
          ->insert([
            'UpdateDate' => date('Y-m-d'),
            'UpdateTime' => date('H:i:s'),
            'EmployeeID' => auth()->user()->Emp_No
          ]);

        return redirect()->route('faculty-evaluation-export');
      }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
      }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
      }
    }

    public function count($data, $emp, $q, $star){
        $count = 0;
        foreach($data as $d){
          if ($d->EmployeeID == $emp and $d->QuestionID == $q and $d->Rating == $star){
            $count = $d->cRating;
            break;
          }
        }

        return $count;
    }

    public function countFaculty($data, $emp){
        $count = 0;
        foreach($data as $d){
          if ($d->EmployeeID == $emp){
            $count = $d->countStudent;
            break;
          }
        }

        return $count;
    }

    public function getFeedBackOneFaculty($data = []){

      $campus = session('campus');
      if (!empty($data['Campus'])){
        $campus = $data['Campus'];
      }

      $ress = FacultyEvaluationFeedback::where('Campus', $campus)
        ->select('EmployeeID', 'Feedback')
        ->where('SchoolYear', $data['SchoolYear'])
        ->where('Semester', $data['Semester'])
        ->groupby('EmployeeID')
        ->groupby('Feedback')
        ->get();


      return $ress;
  }


    public function getRatingOneFaculty($data = []){
        $tbl = "facultyevaluationresult".$data['SchoolYear'].$data['Semester'];

        $campus = session('campus');
        if (!empty($data['Campus'])){
          $campus = $data['Campus'];
        }

        if (empty($data['EmployeeID'])){
          $ress = DB::connection('evaluation')->table($tbl.' as fe')
          ->select('ff.GroupID','fe.EmployeeID', DB::connection('evaluation')->raw('AVG(fe.Rating) as avgRating'))
          ->leftjoin('facultyevaluationform as ff', 'fe.QuestionID', '=', 'ff.id')
          ->where('fe.Campus', $campus)
          ->groupby('ff.GroupID')
          ->groupby('fe.EmployeeID')
          ->orderby('ff.GroupID')
          ->orderby('ff.Sequence')
          ->get();
        }else{
          $ress = DB::connection('evaluation')->table($tbl.' as fe')
          ->select('ff.GroupID','fe.EmployeeID', DB::connection('evaluation')->raw('SUM(fe.Rating) as sumRating'))
          ->leftjoin('facultyevaluationform as ff', 'fe.QuestionID', '=', 'ff.id')
          ->where('fe.Campus', $campus)
          ->where('fe.EmployeeID', $data['EmployeeID'])
          ->groupby('ff.GroupID')
          ->groupby('fe.StudentNo')
          ->groupby('fe.ScheduleID')
          ->get();
        }

        return $ress;
    }

    public function analytics(Request $request){
      try{

        $id = Crypt::decryptstring($request->id);

        $sched = FacultyEvaluationSchedule::where("id",$id)
          ->first();

        if (empty($sched))
          throw new Exception("Schedule not found.");

        $cc = "courseoffering".$sched->SchoolYear.$sched->Semester;
        $grades = "grades".$sched->SchoolYear.$sched->Semester;
        $rating = "facultyevaluationresult".$sched->SchoolYear.$sched->Semester;

        if (!Schema::connection(strtolower(session('campus')))->hasTable($cc)) {
          throw new Exception("Table CC not found.");
        }

        if (!Schema::connection('evaluation')->hasTable($rating)) {
          throw new Exception("Table facultyevaluationresult not found.");
        }

        if (!Schema::connection(strtolower(session('campus')))->hasTable($grades)) {
          throw new Exception("Table grades not found.");
        }

        // $totalStudents = DB::connection(strtolower(session('campus')))
        //         ->table('registration as r')
        //         ->select('r.Course','s.Sex','c.accro',DB::connection(strtolower(session('campus')))->raw('count(r.id) as countStudent'))
        //         ->leftjoin('students as s', 'r.StudentNo', '=','s.StudentNo')
        //         ->leftjoin('course as c', 'r.Course', '=','c.id')
        //         ->where("r.finalize", 1)
        //         ->where("r.SchoolYear", $sched->SchoolYear)
        //         ->where("r.Semester", $sched->Semester)
        //         ->groupby('r.SchoolLevel')
        //         ->groupby('c.accro')
        //         ->groupby('Course')
        //         ->groupby('Sex')
        //         ->orderby("r.SchoolLevel", "DESC")
        //         ->orderby("c.accro")
        //         ->get();
        // // dd($totalStudents);
        // $course_array = [];
        // foreach($totalStudents as $totalStudent){
        //     if (!in_array($totalStudent->accro, $course_array)){
        //       $course_array[] = $totalStudent->accro;
        //     }
        // }
        // $course_value = [];
        // foreach($course_array as $course_v){
        //     $out = 0;
        //     foreach($totalStudents as $totalStudent){
        //       if (strtolower($course_v) == strtolower($totalStudent->accro)){
        //         $out += $totalStudent->countStudent;
        //       }
        //     }
        //     $course_value[] = $out;
        // }
        // // dd($course_value);
        // $getStudents = DB::connection('evaluation')
        //         ->table($rating.' as ra')
        //         ->select('StudentNo')
        //         ->where("ra.Campus", session('campus'))
        //         ->groupBy('ra.StudentNo')
        //         ->pluck('StudentNo')->toArray();

        // $results = DB::connection(strtolower(session('campus')))
        //     ->table('registration as r')
        //     ->leftjoin('students as s', 'r.StudentNo', '=', 's.StudentNo')
        //     ->leftjoin('course as c', 'r.Course', '=', 'c.id')
        //     ->select('s.Sex','c.accro','r.Course','r.SchoolLevel',DB::connection(strtolower(session('campus')))->raw('count(r.id) as countStudent'))
        //     ->where("r.SchoolYear", $sched->SchoolYear)
        //     ->where("r.finalize", 1)
        //     ->where("r.Semester", $sched->Semester)
        //     ->whereIn('r.StudentNo',$getStudents)
        //     ->groupby('r.SchoolLevel')
        //     ->groupby('r.Course')
        //     ->groupby('c.accro')
        //     ->groupby('s.Sex')
        //     ->orderby('r.SchoolLevel', 'DESC')
        //     ->orderby('c.accro')
        //     ->orderby('s.Sex')
        //     ->get();

        // $student_part = [];
        // $male_array = [];
        // $female_array = [];
        // foreach($course_array as $course_v){
        //     $out = 0;
        //     $male = 0;
        //     $female = 0;
        //     foreach($results as $totalStudent){
        //       if (strtolower($course_v) == strtolower($totalStudent->accro)){
        //         if (strtolower($totalStudent->Sex) == 'm'){
        //           $male += $totalStudent->countStudent;
        //         }else{
        //           $female += $totalStudent->countStudent;
        //         }
        //         $out += $totalStudent->countStudent;
        //       }
        //     }
        //     $student_part[] = $out;
        //     $male_array[] = $male;
        //     $female_array[] = $female;
        // }
        // $countFaulties = DB::connection('evaluation')
        //     ->table($rating.' as ra')
        //     ->select('EmployeeID', DB::connection('evaluation')->raw('count(*) as countStudent'))
        //     ->where("ra.Campus", session('campus'))
        //     ->groupBy('ra.EmployeeID')
        //     ->get();

        $faculties = DB::connection(strtolower(session('campus')))
        ->table('registration as r')
        ->leftjoin($grades.' as g', 'r.RegistrationID', '=', 'g.gradesid')
        ->leftjoin($cc.' as cc', 'g.courseofferingid', '=', 'cc.id')
        ->leftjoin('employees as e', 'cc.teacher', '=', 'e.id')
        ->select('cc.teacher',DB::connection(strtolower(session('campus')))->raw('CONCAT(e.LastName,", ",e.FirstName) as Faculty'),DB::connection(strtolower(session('campus')))->raw('count(cc.id) as countStudent'))
        ->where("r.finalize", 1)
        ->where("r.SchoolYear", $sched->SchoolYear)
        ->where("r.Semester", $sched->Semester)
        ->groupby('cc.teacher')
        ->groupby('e.LastName')
        ->groupby('e.FirstName')
        ->orderby('e.LastName')
        ->orderby('e.FirstName')
        ->get();

        $FEResults = DB::connection('evaluation')
            ->table($rating.' as ra')
            ->select('ra.Campus', DB::raw('COUNT(*) / 20 as Rated'))
            ->groupby('ra.Campus')
            ->get();

        $ratesResults = $this->getRatingOneFaculty(['SchoolYear' => $sched->SchoolYear, "Semester" => $sched->Semester]);

        $feeParticipateds = DB::connection('evaluation')
        ->table($rating.' as ra')
        ->select('ra.EmployeeID','ra.ScheduleID', DB::raw('COUNT(ra.StudentNo) / 20 as Rated'))
        ->where('Campus', session('campus'))
        ->groupby('ra.Campus')
        ->groupby('ra.EmployeeID')
        ->groupby('ra.ScheduleID')
        ->get();

        $enrolledCampus = [];
        $enrolled = [];
        foreach(GENERAL::Campuses() as $index => $campus){
          $enrolleeQ = DB::connection(strtolower($index))
            ->table('registration as r')
            ->select(DB::connection(strtolower($index))->raw('COUNT(g.courseofferingid) as enrollee'))
            ->leftjoin($grades.' as g', 'r.RegistrationID', '=', 'g.gradesid')
            ->where('r.finalize', 1)
            ->where('r.SchoolYear', $sched->SchoolYear)
            ->where('r.Semester', $sched->Semester)
            ->first();


            $enrolled[] = $enrolleeQ  ->enrollee;
        }

        $campuses = [];
        $participated = [];

        foreach(GENERAL::Campuses() as $index => $campus){
          $campuses[] = "'".$index."'";
          $part = 0;
          foreach($FEResults as $FEResult){
            if ($index == $FEResult->Campus){
              $part = $FEResult->Rated;
            }
          }
          $participated[] = $part;
        }

        //  $enrolled = DB::connection(strtolower(session('campus')))
        //     ->table('registration as r')
        //     ->leftjoin('students as s', 'r.StudentNo', '=', 's.StudentNo')
        //     ->leftjoin('course as c', 'r.Course', '=', 'c.id')
        //     ->select('s.Sex','c.accro','r.Course','r.SchoolLevel',DB::connection(strtolower(session('campus')))->raw('count(r.id) as countStudent'))
        //     ->where("r.SchoolYear", $sched->SchoolYear)
        //     ->where("r.finalize", 1)
        //     ->where("r.Semester", $sched->Semester)
        //     ->groupby('r.SchoolLevel')
        //     ->groupby('r.Course')
        //     ->groupby('c.accro')
        //     ->groupby('s.Sex')
        //     ->orderby('r.SchoolLevel', 'DESC')
        //     ->orderby('c.accro')
        //     ->orderby('s.Sex')
        //     ->get();


        //     dd($enrolled->sum('countStudent'));

        // SELECT  from facultyevaluationresult20241
        // group by , ;

        $pageTitle = "Faculty Evaluation Analytics (".GENERAL::setSchoolYearLabel($sched->SchoolYear,$sched->Semester)." - ".GENERAL::Semesters()[$sched->Semester]['Short'].")";
        $headerAction = '';

        return view('slsu.facultyevaluation.analytics',compact('campuses','participated','enrolled','faculties','feeParticipateds','ratesResults'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'SchoolYear' => $sched->SchoolYear,
            'Semester' => $sched->Semester
          ]);
        // return view('slsu.facultyevaluation.analytics',compact('totalStudents','results','faculties','countFaulties','course_array','course_value','student_part','male_array','female_array'), [
        //   'pageTitle' => $pageTitle,
        //   'headerAction' => $headerAction
        // ]);
      }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
      }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
      }
    }

    // public function allresults(Request $request){

    //   $pageTitle = "All Faculty Evaluation";
    //   $headerAction = '';

    //   $campus = "";
    //   $period = "";
    //   $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
    //     ->orderby("Semester")
    //     ->get();

    //   $lists = [];
    //   $results = [];
    //   $perSchedAVGs = [];
    //   if (!empty($request->Period)){

    //     $period = $request->Period;
    //     $sched = FacultyEvaluationSchedule::orderby("SchoolYear")
    //       ->orderby("Semester")
    //       ->first();


    //     // $tbl = "facultyevaluationresult".$sched->SchoolYear.$sched->Semester;
    //     $cc = "courseoffering".$sched->SchoolYear.$sched->Semester;

    //     if (auth()->user()->AllowSuper == 1){
    //       $campus = $request->Campus;
    //       $depts = DB::connection(strtolower($campus))
    //         ->table('department')
    //         ->where("Active", 0)
    //         ->pluck('id')->toArray();
    //       // dd($depts);
    //     }else{
    //       $campus = session('campus');
    //       $depts = DB::connection(strtolower($campus))
    //           ->table('department')
    //           ->where('DepartmentHead', auth()->user()->Emp_No)
    //           ->where("Active", 0)
    //           ->pluck('id')->toArray();
    //     }


    //     // if (strtolower(auth()->user()->AccountLevel) == "administrator"){
    //     //   $campus = session('campus');
    //     //   $depts = Department::where("Active", 0)
    //     //       ->get();
    //     // }else{
    //     // diri esud ang sa
    //     // }

    //     $feedbacks =

    //     $lists = DB::connection(strtolower($campus))
    //         ->table($cc.' as cc')
    //         ->select('cc.teacher', 'e.LastName', 'e.FirstName', 'd.DepartmentName')
    //         ->leftjoin('employees as e', 'cc.teacher', '=', 'e.id')
    //         ->leftjoin('department as d', 'e.Department', '=', 'd.id')
    //         ->where('cc.teacher', '<>', 0)
    //         ->whereIn('e.Department', $depts)
    //         ->groupby('cc.teacher')
    //         ->groupby('e.LastName')
    //         ->groupby('e.FirstName')
    //         ->orderby('e.LastName')
    //         ->orderby('e.FirstName')
    //         ->get();

    //     foreach($lists as $list){

    //       $ress = $this->getRatingOneFaculty([
    //         'SchoolYear' => $sched->SchoolYear,
    //         'Semester' => $sched->Semester,
    //         'EmployeeID' => $list->teacher,
    //         'Campus' => $campus
    //       ]);

    //       $cat = [];
    //       $fivecat = [];
    //       $avgfivecat = 0;
    //       $overallavg = 0;
    //       if (count($ress) > 0){
    //         for($x=1;$x<=4;$x++){
    //           $avg = 0;
    //           $five = 0;
    //           $ctr=0;
    //           foreach($ress as $res){
    //             if ($x == $res->GroupID){
    //               $avg += $res->sumRating;
    //               $five += $res->sumRating / 5;
    //               $ctr++;
    //             }
    //           }
    //           $tmpC = $avg / $ctr;
    //           $tmpAv = $five / $ctr;
    //           $avgfivecat +=  ($tmpAv > 5 ? 5 : $tmpAv);
    //           $cat[] = ($tmpC > 25 ? 25 : $tmpC);
    //           $fivecat[] = ($tmpAv > 5 ? 5 : $tmpAv);
    //           $overallavg += ($avg / $ctr);
    //         }
    //       }

    //       $feeds = $this->getFeedBackOneFaculty([
    //         'SchoolYear' => $sched->SchoolYear,
    //         'Semester' => $sched->Semester,
    //         'EmployeeID' => $list->teacher,
    //         'Campus' => $campus
    //       ]);
    //       $perSchedAVGs[] = [
    //         'Name' => $list->LastName.', '.$list->FirstName,
    //         'Department' => $list->DepartmentName,
    //         'Category' => $cat,
    //         'FiveCat' => $fivecat,
    //         'AVGFiveCat' => $avgfivecat / 4,
    //         'Average' => $overallavg,
    //         'SchoolYear' => $sched->SchoolYear,
    //         'Semester' => $sched->Semester,
    //         'Feedbacks' => $feeds
    //       ];
    //     }
    //   }


    //   return view('slsu.facultyevaluation.all',compact('scheds','perSchedAVGs'),[
    //     'pageTitle' => $pageTitle,
    //     'headerAction' => $headerAction,
    //     'Campus' => $campus,
    //     'Period' => $period
    //   ]);
    // }

    public function allresults(Request $request){

      $pageTitle = "All Faculty Evaluation";
      $headerAction = '';

      $campus = "";
      $period = "";
      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear")
        ->orderby("Semester")
        ->get();

      $statuss = Employee::select('EmploymentStatus')
          ->groupby('EmploymentStatus')
          ->orderby('EmploymentStatus')
          ->get();


      $lists = [];
      $results = [];
      $perSchedAVGs = [];
      $feeds = [];
      $deptlists = [];
      $deptid = 0;
      $statusstr = '';
      $facultyname = '';
      if (!empty($request->Period)){

        $period = $request->Period;
        $sched = FacultyEvaluationSchedule::orderby("SchoolYear")
          ->orderby("Semester")
          ->where('id', $period)
          ->first();

        // $tbl = "facultyevaluationresult".$sched->SchoolYear.$sched->Semester;
        $cc = "courseoffering".$sched->SchoolYear.$sched->Semester;
        $summary = "facultyevaluationsummary".$sched->SchoolYear.$sched->Semester;

        if (auth()->user()->AllowSuper == 1 or ROLE::isVPAA() or ROLE::isPresident()){
          $campus = $request->Campus;
          $depts = DB::connection(strtolower($campus))
            ->table('department')
            ->where("Active", 0);

          if (!empty($request->Department)){
            $deptid = $request->Department;
            $depts = $depts->where('id', $request->Department);
          }
          $depts = $depts->pluck('id')->toArray();

          // dd($depts);
        }else{
          $campus = session('campus');
          $depts = DB::connection(strtolower($campus))
              ->table('department')
              ->where('DepartmentHead', auth()->user()->Emp_No)
              ->where("Active", 0)
              ->pluck('id')->toArray();
        }

        if (Schema::connection("evaluation")->hasTable($summary)) {
          $perSchedAVGs = DB::connection('evaluation')
            ->table($summary)
            ->where('Campus', $campus)
            ->WhereIn('DepartmentID', $depts);

          if (!empty($request->Status)){
            $statusstr = $request->Status;
            $perSchedAVGs = $perSchedAVGs->where('EmploymentStatus', $request->Status);
          }

          if (!empty($request->Faculty)){
            $facultyname = $request->Faculty;
            $perSchedAVGs = $perSchedAVGs->where(function($q) use($facultyname) {
              $q->orWhere('LastName', 'LIKE', '%'.$facultyname.'%')
                ->orwhere('FirstName', 'LIKE', '%'.$facultyname.'%');
            });
          }

          $perSchedAVGs = $perSchedAVGs->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        }

        $deptlists = DB::connection(strtolower($campus))
            ->table('department')
            ->where('Active', 0)
            ->orderby('DepartmentName')
            ->get();

        $feeds = $this->getFeedBackOneFaculty([
          'SchoolYear' => $sched->SchoolYear,
          'Semester' => $sched->Semester,
          'Campus' => $campus
        ]);
      }

      return view('slsu.facultyevaluation.all',compact('scheds','perSchedAVGs','feeds','statuss','deptlists'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'Campus' => $campus,
        'Period' => $period,
        'DepartmentID' => $deptid,
        'StatusStr' => $statusstr,
        'FacultyName' => $facultyname
      ]);
    }

    public function indexvpaa(){
      $pageTitle = "Performace by Campus";
      $headerAction = '';

      $scheds = FacultyEvaluationSchedule::orderby("SchoolYear", "DESC")
      ->orderby("Semester", "DESC")
      ->get();
      $one = "";
      $sy = "";
      $sem = "";
      $tblsummary = "";
      $results = [];
      $tblEnrolled = [];
      if (count($scheds) > 0){
        $one = $scheds[0]->id;
        $sy = $scheds[0]->SchoolYear;
        $sem = $scheds[0]->Semester;

        $tblsummary = "facultyevaluationsummary".$sy.$sem;
        $results = DB::connection('evaluation')
          ->table($tblsummary)
          ->get();

        $tblEnrolled =  DB::connection('evaluation')
          ->table('facultyevaluationenrolled')
          ->where('SchoolYear', $sy)
          ->where('Semester', $sem)
          ->first();
        // dd($tblEnrolled->SGResult);

      }
      return view('slsu.facultyevaluation.indexvpaa',compact('scheds','results','tblEnrolled'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'ID' => $one
      ]);
    }


    // CREATE TABLE
    public function createTableSummary($tname)
    {
      if (!Schema::connection("evaluation")->hasTable($tname)) {
          Schema::connection("evaluation")->create($tname, function ($table) {
              $table->increments('id');
              $table->bigInteger('EmployeeID');
              $table->bigInteger('DepartmentID');
              $table->string('DepartmentName');
              $table->string('EmploymentStatus');
              $table->string('LastName');
              $table->string('FirstName');
              $table->string('MiddleName');
              $table->integer('SchoolYear');
              $table->integer('Semester');
              $table->string('Campus');
              $table->string('ScheduleID');
              $table->double('C1',15,2);
              $table->double('C2',15,2);
              $table->double('C3',15,2);
              $table->double('C4',15,2);
              $table->double('P1',15,2);
              $table->double('P2',15,2);
              $table->double('P3',15,2);
              $table->double('P4',15,2);
              $table->timestamps();
        });
      }
    }

    public function createTableLastUpdate()
    {
      if (!Schema::connection("evaluation")->hasTable('facultyevaluationupdate')) {
          Schema::connection("evaluation")->create('facultyevaluationupdate', function ($table) {
              $table->increments('id');
              $table->date('UpdateDate');
              $table->date('UpdateTime');
              $table->bigInteger('EmployeeID');
              $table->timestamps();
        });
      }
    }


}
