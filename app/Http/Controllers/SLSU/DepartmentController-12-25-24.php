<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\RegistrationLock;
use App\Http\Controllers\SLSU\ClearanceController;
use App\Http\Controllers\SLSU\ProspectosController;
use App\Http\Controllers\SLSU\ScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Role;
use App\Models\Student;
use App\Models\Course;
use App\Models\Registration;
use App\Models\Grades;
use App\Models\Status;
use App\Models\MaxLimit;
use App\Models\Employee;

use Crypt;
use GENERAL;


class DepartmentController extends Controller
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
    //clearance functions
    public function search(Request $request): JsonResponse
    {
        $str = $request->str;
        $data = Student::select(DB::connection(strtolower(session('campus')))->raw('concat(StudentNo, " - ",LastName, ", ",FirstName) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("LastName","LIKE","%{$str}%")
                      ->whereOr("FirstName","LIKE","%{$str}%")
                      ->whereOr("StudentNo","LIKE","%{$str}%");
                })
                ->pluck('Name');

        return response()->json($data);
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

      return view('slsu.department.latinhonors', compact('courses'), [
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


        return view('slsu.department.gwa', compact('all'));
    }

    public function enrolment(Request $request){

      $pageTitle = "Completed STEP 1 (SUBMIT CONSENT)";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      try{

        $lock = new RegistrationLock([
          'sy' => $this->sy,
          'sem' => $this->sem
        ]);

        if (!$lock->isOKToEncode())
          throw new Exception("Enrolment for ".GENERAL::setSchoolYearLabel($this->sy,$this->sem) . " ". GENERAL::Semesters()[$this->sem]['Long']. " is closed last ".date('F j, Y', strtotime($lock->getDateEnd())));

        $step1s = Registration::query();
        $step1s = $step1s->where("finalize", 2)
            ->select("registration.*", "LastName", "FirstName", "accro", "course_major")
            ->leftjoin("students as s", "registration.StudentNo", "=", "s.StudentNo")
            ->leftjoin("course as c", "registration.Course", "=", "c.id")
            ->leftjoin("major as m", "registration.Major", "=", "m.id")
            ->where("registration.SchoolYear", $this->sy)
            ->where("registration.Semester", $this->sem);

        if (!auth()->user()->AllowSuper == 1){
            $assignedprograms = DB::connection(strtolower(session('campus')))
              ->table("accountcourse")
              ->select("CourseID")
              ->where("UserName", strtolower(auth()->user()->Emp_No))
              ->pluck("CourseID")->toArray();

              $step1s = $step1s->whereIn("registration.Course", $assignedprograms);
        }

        $statuss = Status::all();

        $step1s = $step1s->orderby("TESDate")
            ->orderby("TimeTES")
            ->get();

        return view('slsu.department.step2', compact('step1s','statuss'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction
        ]);

      }catch(Exception $e){
        return view('slsu.error.all', [
          'pageTitle' => "Error Processing",
          'Error' => $e->getMessage()
        ]);
      }

    }

    public function proenrolment(Request $request){

        try{

          // $scheds = new ScheduleController();
          // $listScheds = $scheds->lists(['ID' => 2286]);
          $studentno = Crypt::decryptstring($request->id);
          if (empty($studentno))
            throw new Exception("Empty Student Number");

          $one = Student::where("StudentNo", $studentno)->first();
          if (empty($one))
            throw new Exception("Student Number not found");

          $course = $one->Course;
          $major = $one->major;
          $CurNum = $one->cur_num;
          $CourseString = $one->course->accro;
          $SchoolLevel = $one->course->lvl;
          $MajorString = $one->Major->course_major;

          $reg = Registration::where("StudentNo", $studentno)
              ->where("SchoolYear", $this->sy)
              ->where("Semester", $this->sem)
              ->first();

          if (!empty($reg)){
            $course = $reg->Course;
            $major = $reg->Major;
            $SchoolLevel = $reg->course->lvl;
            $CourseString = $reg->course->accro;
            $MajorString = $reg->major->course_major;
            if (!empty($reg->cur_num)){
              $CurNum = $reg->cur_num;
            }
          }


          $pros = new ProspectosController();
          $pros->setCourse($course);
          $pros->setCurNum($CurNum);
          $subjects = $pros->getList();

          $grades = GENERAL::CreateTMPGrades(['StudentNo' => $studentno]);

          $mysubjects = [];
          foreach($grades as $mygrades){
              if ($mygrades['SchoolYear'] == $this->sy and $mygrades['Semester'] == $this->sem){
                  array_push($mysubjects, [
                    "Time1" => $mygrades['Time1'],
                    "Time2" => $mygrades['Time2'],
                    "CourseNo" => $mygrades['CourseNo'],
                    "Unit" => $mygrades['Unit'],
                    "Exempt" => $mygrades['Exempt']]);
              }
          }

          session(['MySubjects' => $mysubjects]);

          $credits = GENERAL::getCreditedSubjects(['StudentNo' => $studentno]);
          $pageTitle = "Encode subject(s)";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

          $toCheck = $course;
          if (!empty($major))
            $toCheck = $major;

          $mUnits = GENERAL::getMaxUnit([
            'Course' => $toCheck,
            'StudentYear' => $reg->StudentYear,
            'Semester' => $this->sem,
            'StudentNo' => $studentno,
            'SchoolYear' => $this->sy,
          ]);

          return view('slsu.enrol.index', compact('subjects','one','grades','credits','reg'),[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'CourseString' => $CourseString,
            'MajorString' => $MajorString,
            'CurNum' => $CurNum,
            'SchoolLevel' => $SchoolLevel,
            'Major' => $major,
            'SchoolYear' => $this->sy,
            'Semester' => $this->sem,
            'MaxLimit' => $mUnits
          ]);

        }catch(Exception $e){
          return view('slsu.error.all', [
            'pageTitle' => "Error Processing",
            'Error' => $e->getMessage()
          ]);
        }catch(DecryptException $e){
          return view('slsu.error.all', [
            'pageTitle' => "Invalid Payload",
            'Error' => $e->getMessage()
          ]);
        }
    }

    public function preregsurvey(){
      $pageTitle = "PRE REGISTRATION SURVEY RESULT";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $results = DB::connection(strtolower(session('campus')))
        ->table("prereg_limit as pl")
        ->select('pl.*', 'm.course_major')
        ->leftjoin("major as m", "pl.Major", "=", "m.id")
        ->get();

      return view('slsu.department.report.preregsurvey', compact('results'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
      ]);
    }

    public function propreregsearch(Request $request){
        try{
            $SchoolYear = $request->SchoolYear;
            $Semester = $request->Semester;
            if (empty($SchoolYear))
              throw new Exception("Please select school year");

            if (empty($Semester))
              throw new Exception("Please select semester");


            $limits = DB::connection(strtolower(session('campus')))
              ->table("prereg_limit as pl")
              ->select("pl.*", "m.course_major")
              ->leftjoin("major as m", "pl.Major", "=", "m.id")
              ->orderby("Major")
              ->orderby("StudentYear")
              ->orderby("Section")
              ->get();

            $counts = DB::connection(strtolower(session('campus')))
              ->table("prereg_selected as ps")
              ->select("ps.Major", "ps.StudentYear", "ps.Section", DB::connection(strtolower(session('campus')))->raw("count(id) as cCount"))
              ->groupby("Major")
              ->groupby("StudentYear")
              ->groupby("Section")
              ->where("ps.SchoolYear", $SchoolYear)
              ->where("ps.Semester", $Semester)
              ->get();

            return view('slsu.department.report.preregsurveylist', compact('limits','counts'),[
                'SchoolYear' => $SchoolYear,
                'Semester' => $Semester
            ]);

        }catch(Exception $e){
            return GENERAL::Error($e->getMessage());
        }
    }

    public function propreregsearchlist(Request $request){
        try{
          $id = Crypt::decryptstring($request->id);
          $sy = Crypt::decryptstring($request->sy);
          $sem = Crypt::decryptstring($request->sem);


          $limit = DB::connection(strtolower(session('campus')))
          ->table("prereg_limit as pl")
          ->select("pl.*", "m.course_major")
          ->leftjoin("major as m", "pl.Major", "=", "m.id")
          ->where("pl.id", $id)
          ->first();


          if (empty($limit))
            throw new Exception("Invalid data");


          $students = DB::connection(strtolower(session('campus')))
            ->table("prereg_selected as ps")
            ->select("ps.*", "s.FirstName", "s.LastName", "m.course_major","s.ContactNo")
            ->leftjoin("major as m", "ps.Major", "=", "m.id")
            ->leftjoin("students as s", "ps.StudentNo", "=", "s.StudentNo")
            ->where("ps.Major", $limit->Major)
            ->where("ps.StudentYear", $limit->StudentYear)
            ->where("ps.Section", $limit->Section)
            ->where("ps.SchoolYear", $sy)
            ->where("ps.Semester", $sem)
            ->orderby("created_at")
            ->get();

          $studentnos = $students->pluck('StudentNo')->toArray();

          $regs = Registration::select("finalize", "StudentNo")
              ->where("SchoolYear", $sy)
              ->where("Semester", $sem)
              ->whereIn("StudentNo", $studentnos)
              ->get();

          return view('slsu.department.report.preregsurveyliststudent',
            compact('students','regs'));

        }catch(Exception $e){
          return GENERAL::Error($e->getMessage());
        }catch(DecryptException $e){
          return GENERAL::Error($e->getMessage());
        }
    }

    public function employee(){

        $my = Employee::find(auth()->user()->Emp_No);
        $lists = Employee::where("Department", $my->Department)
            ->where("isActive", "Yes")
            ->orderBy("LastName")
            ->orderBy("FirstName")
            ->get();

        $pageTitle = "Employee Masterlist";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.department.employee', compact('lists'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction
        ]);
    }

    public function destroyemp(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

        $one = Employee::find($id);
        if (empty($one))
          throw new Exception("Employee not found.");

        $data = [
          'isActive' => "No",
          'deleted_at' => now()
        ];
        $del = Employee::where("id", $id)
          ->update($data);
        if (!$del)
        throw new Exception("Unable to delete employee.");
      }catch(Exception $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }catch(DecryptException $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }
    }

    public function edit(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

        $one = Employee::find($id);
        if (empty($one))
          throw new Exception("Employee not found.");

        return response()->json($one);
      }catch(Exception $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }catch(DecryptException $e){
        return response()->json(['errors' => $e->getMessage()], 400);
      }
    }

}
