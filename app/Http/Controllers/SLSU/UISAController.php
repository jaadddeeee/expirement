<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\ScheduleController;
use App\Http\Controllers\SLSU\ClearanceController;
use App\Http\Controllers\SLSU\SMSController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;
use GENERAL;
use Preference;

use App\Models\Role;
use App\Models\Student;
use App\Models\Registration;
use App\Models\Prospectos;

class UISAController extends Controller
{
    protected $objSched;
    protected $sms;

    public function __construct(){
      $this->objSched = new ScheduleController();
      $this->sms = new SMSController();
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

    public function requestedsubject(){
      $pageTitle = "Add Requested Subject";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      return view('slsu.uisa.requested-subject', [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function prorequestedsubject(Request $request){
        try{

          $clsprefs = new Preference();
          $prefs = $clsprefs->GetDefaults(['RequestAmountPT','RequestAmountRG']);

          $courscode = $request->coursecode;
          $SchoolYear = $request->SchoolYear;
          $Semester = $request->Semester;

          if (empty($courscode))
            throw new Exception("Empty Course Code");

          if (empty($SchoolYear))
          throw new Exception("Please select school year");

          if (empty($Semester))
          throw new Exception("Please select semester");

          $cctable = "courseoffering".$SchoolYear.$Semester;
          $grades = "grades".$SchoolYear.$Semester;

          if (!Schema::connection(strtolower(session('campus')))->hasTable($cctable))
            throw new Exception("Invalid schoolyear/semester");

          $ccs = DB::connection(strtolower(session('campus')))
              ->table($cctable. " as cc")
              ->select("cc.*","t.courseno", "t.coursetitle", "c.accro",
                "st1.tym as Time1","st2.tym as Time2",
                "e.FirstName", "e.LastName", "e.EmploymentStatus")
              ->leftjoin("course as c", "cc.course", "=", "c.id")
              ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
              ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
              ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
              ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
              ->where("cc.coursecode", $courscode)
              ->first();

          if (empty($ccs))
            throw new Exception("Invalid Course Code");

          if (empty($ccs->RequireReqForm))
            throw new Exception("Code not tag as requested.");

          $reqs = DB::connection(strtolower(session('campus')))
              ->table($cctable. " as cc")
              ->select("g.id as GID", "cc.*", "s.StudentNo", "s.LastName", "s.FirstName", "t.courseno", "t.coursetitle", "c.accro", "t.lec", "t.lab")
              ->leftjoin($grades." as g", "cc.id", "=", "g.courseofferingid")
              ->leftjoin("students as s", "g.StudentNo", "=", "s.StudentNo")
              ->leftjoin("course as c", "s.Course", "=", "c.id")
              ->leftjoin("transcript as t", "g.sched", "=", "t.id")
              ->where("cc.coursecode", $courscode)
              ->orderby("s.LastName")
              ->orderby("s.FirstName")
              ->get();

          $divisor = count($reqs);
          $honorariumpt = $clsprefs->GetDefaultValue($prefs, "RequestAmountPT");
          $honorariumrg = $clsprefs->GetDefaultValue($prefs, "RequestAmountRG");

          $job = "pt";

          if (!empty($ccs->EmploymentStatus)){
              if (strtolower($ccs->EmploymentStatus) == "permanent - faculty"){
                $job = "reg";
              }

              if (strtolower($ccs->EmploymentStatus) == "temporary - faculty"){
                $job = "reg";
              }
          }
          $waive = 0;
          if (!isset($ccs->isWaive)){
            $waive = 0;
          }else{
            $waive = $ccs->isWaive;
          }

          if ($waive == 1){
            $feeperstudent = "waive";
          }else{

            if ($job == 'pt'){
              if (empty($honorariumpt)){
                $feeperstudent = 0;
              }else{
                $lab = (empty($reqs[0]->lab)?0:$reqs[0]->lab);
                $lec = (empty($reqs[0]->lec)?0:$reqs[0]->lec);
                $hours = ($lab * 3) + $lec ;
                $feeperstudent = (($honorariumpt * $hours) * 18) / $divisor;
              }
            }else{
              if (empty($honorariumrg)){
                $feeperstudent = 0;
              }else{
                $lab = (empty($reqs[0]->lab)?0:$reqs[0]->lab);
                $lec = (empty($reqs[0]->lec)?0:$reqs[0]->lec);
                $hours = ($lab * 3) + $lec;
                $feeperstudent = (($honorariumrg * $hours) * 18) / $divisor;
              }
            }
          }
          // dd($feeperstudent);
          return view('slsu.uisa.requested-list', compact('reqs','ccs'),
            [
              'CourseCode' => $courscode,
              'SchoolYear' => $SchoolYear,
              'Semester' => $Semester,
              'feeperstudent' => $feeperstudent
            ]);

        }catch(DecryptException $e){
          return GENERAL::Error($e->getMessage());
        }catch(Exception $e){
          return GENERAL::Error($e->getMessage());
        }
    }

    public function searchstudent(Request $request){
      try{
        $tmps = $request->allsearch;
        $hiddenCode = $request->hiddenCode;
        $hiddenSchoolyear = $request->hiddenSchoolyear;
        $hiddenSemester = $request->hiddenSemester;

        if (empty($hiddenCode))
          throw new Exception("No schedule selected");

        if (empty($hiddenSchoolyear))
          throw new Exception("No school year selected");

        if (empty($hiddenSemester))
          throw new Exception("No semester selected");

        if (empty($tmps))
          throw new Exception("No student selected");

        $dataTMP = explode(" - ", $tmps);

        if (sizeof($dataTMP) != 2)
          throw new Exception("Invalid Student format.");


        $one = Student::where("StudentNo", $dataTMP[0])->first();
        $reg = Registration::where("StudentNo", $dataTMP[0])
            ->where("SchoolYear", $hiddenSchoolyear)
            ->where("Semester", $hiddenSemester)
            ->first();

        if (empty($one))
          throw new Exception("Student not found. ");

        $course = $one->Course;
        $CurNum = $one->cur_num;
        $Major = $one->major;
        if (!empty($reg)){
          $course = $reg->Course;
          $CurNum = $reg->cur_num;
          $Major = $reg->Major;

          if (empty($CurNum))
            $CurNum = $one->cur_num;
        }



        $cctable = "courseoffering".$hiddenSchoolyear.$hiddenSemester;

        $getSched = DB::connection(strtolower(session('campus')))
          ->table($cctable. " as cc")
          ->select("t.id", "t.courseno")
          ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
          ->where("cc.coursecode", $hiddenCode)
          ->first();

        if (empty($getSched))
          throw new Exception("Invalid Schedule");

        $pros = Prospectos::where("course", $course)
            ->where("cur_num", $CurNum)
            ->where("hide", "<>", 1)
            ->orderby("courseno")
            ->get();
        // dd('f');
        return view('slsu.uisa.info', compact('one','reg','pros','getSched'),[
          'Major' => $Major,
          'CurNum' => $CurNum
        ]);

      }catch(DecryptException $e){
        return GENERAL::Error($e->getMessage());
      }catch(Exception $e){
        return GENERAL::Error($e->getMessage());
      }
    }

    public function saverequestedsubject(Request $request){
      try{

        $tmps = $request->allsearch;
        $hiddenCode = $request->hiddenCode;
        $hiddenSchoolyear = $request->hiddenSchoolyear;
        $hiddenSemester = $request->hiddenSemester;
        $RequestedSubject = $request->RequestedSubject;

        if (empty($tmps))
          throw new Exception("No student selected");

        if (empty($hiddenCode))
          throw new Exception("No schedule selected");

        if (empty($hiddenSchoolyear))
          throw new Exception("No school year selected");

        if (empty($hiddenSemester))
          throw new Exception("No semester selected");

        if (empty($RequestedSubject))
          throw new Exception("No subject selected");

        $dataTMP = explode(" - ", $tmps);

        if (sizeof($dataTMP) != 2)
          throw new Exception("Invalid Student format.");

        //Transcript table
        $subject = Prospectos::where("pri",$RequestedSubject)->first();
        if (empty($subject))
          throw new Exception("Invalid Subject.");

        $one = Student::where("StudentNo", $dataTMP[0])->first();
        $reg = Registration::where("StudentNo", $dataTMP[0])
            ->where("SchoolYear", $hiddenSchoolyear)
            ->where("Semester", $hiddenSemester)
            ->first();

        if (empty($one))
          throw new Exception("Student not found. ");

        // if (!empty($reg) and $reg->finalize == 1)
        //   throw new Exception("Student is already finalize. ");


        $course = $one->Course;
        $CurNum = $one->cur_num;
        $Major = $one->major;

        if (!empty($reg)){
          $course = $reg->Course;
          $CurNum = $reg->cur_num;
          $Major = $reg->Major;
        }

        //check prerequisite
        $requi = GENERAL::isPrerequisiteOK($subject->prerequsite,$subject->id,$dataTMP[0]);
        if (!$requi)
          throw new Exception("Student has a problem with prerequisite.");
        //get CC
        $cc = "courseoffering".$hiddenSchoolyear.$hiddenSemester;
        $grades = "grades".$hiddenSchoolyear.$hiddenSemester;

        if (!Schema::connection(strtolower(session('campus')))->hasTable($cc))
          throw new Exception("Invalid Schedule table");

        $cc_one = DB::connection(strtolower(session('campus')))->table($cc." as cc")
            ->select("cc.id", "cc.avail", "cc.Scheme", "st1.tym as Time1",
            "st2.tym as Time2", "t.courseno", "cc.coursecode",
            't.coursetitle','t.units','t.exempt')
            ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
            ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
            ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
            ->where("cc.coursecode", $hiddenCode)
            ->first();

        if (empty($cc_one))
          throw new Exception("Invalid Course Code");

        $units = $cc_one->units;
        if ($cc_one->exempt == 1)
          $units = 0;

        if (!empty($reg)){

          //check if na enrolled na
          $subexist = GENERAL::isSubjectExist([
            'SchoolYear' => $hiddenSchoolyear,
            'Semester' => $hiddenSemester,
            'SubjectID' => $subject->id,
            'StudentNo' => $dataTMP[0]
          ]);

          if ($subexist['Error'])
            throw new Exception("Error in grade table");

          if ($subexist['Exist'])
            throw new Exception("Subject already exist");

          // //compare max units
          $toenrolledunits = GENERAL::getEnrolledDB([
            'SchoolYear' => $hiddenSchoolyear,
            'Semester' => $hiddenSemester,
            'StudentNo' => $dataTMP[0]
          ]);

          if ($toenrolledunits['Error'])
            throw new Exception("Invalid grade table");

          $totalUnits = $toenrolledunits['Units'] + $units;

          $toCheck = $reg->Course;
          if (!empty($reg->Major))
            $toCheck = $reg->Major;

          $maxlimit = GENERAL::getMaxUnit([
            'Course' => $toCheck,
            'StudentYear' => $reg->StudentYear,
            'Semester' => $hiddenSemester,
            'StudentNo' => $dataTMP[0],
            'SchoolYear' => $hiddenSchoolyear,
          ]);

          if (empty($maxlimit))
            throw new Exception("Max Limit not set for ".$reg->StudentYear."-".$hiddenSemester);

          if ($totalUnits > $maxlimit){
            if (!empty($one->ContactNo)){
                $msg = date('dM G:i')."\nHello ".strtoupper($one->FirstName)."!\n\nPetition subject ".$subject->courseno." was not added due to over units. Visit UISA/CISA to submit the approved overload.";
                $this->sms->send($one->ContactNo, $msg);
            }
            throw new Exception("Student has reached the maximum allowable unit of ".$maxlimit);
          }


          //check conflict
          $hasConflict = $this->objSched->isTimeConflict($toenrolledunits['MySubjects'], $cc_one);
          if ($hasConflict['Conflict']){
            $out = "";
            foreach($hasConflict['Message'] as $sConflict){
                if (empty($out)){
                  $out = $sConflict['CourseNo'] . " Sched ".$sConflict['Schedule'].": (".$sConflict['Time'].")";
                }else{
                  $out .= $sConflict['CourseNo'] . " Sched ".$sConflict['Schedule'].": (".$sConflict['Time'].")";
                }
            }
            if (!empty($one->ContactNo)){
              $msg = date('dM G:i')."\nHello ".strtoupper($one->FirstName)."!\n\nPetition subject ".$subject->courseno." was not added due to conflict: ".$out;
              $this->sms->send($one->ContactNo, $msg);
            }
            throw new Exception("Conflict to ".$out);
          }
        }

        if (empty($reg)){
            $regid = date('Ymd').time().$dataTMP[0];
            $data = [
              'RegistrationID' => $regid,
              'SchoolLevel' => $one->course->lvl,
              'StudentNo' => $dataTMP[0],
              'SchoolYear' => $hiddenSchoolyear,
              'StudentYear' =>  1,
              'Semester' => $hiddenSemester,
              'DateEncoded' => date('Y-m-d'),
              'EncodedBy' => 'UISA',
              'Course' => $course,
              'Major' => $Major,
              'finalize' => 2,
              'cur_num' => $one->cur_num,
              'WhereEnrolled' => 'online'
            ];
            $save = Registration::insert($data);

            $dataSave = [
              'gradesid' =>$regid,
              'courseofferingid' => $cc_one->id,
              'sched' => $subject->id,
              'StudentNo' => $dataTMP[0]
            ];

            $save = DB::connection(strtolower(session('campus')))->table($grades)
              ->insert($dataSave);
        }else{

            $dataSave = [
              'gradesid' => $reg->RegistrationID,
              'courseofferingid' => $cc_one->id,
              'sched' => $subject->id,
              'StudentNo' => $dataTMP[0]
            ];

            $save = DB::connection(strtolower(session('campus')))->table($grades)
              ->insert($dataSave);
        }

        if (!$save)
          throw new Exception("Unable to save requested subject.");

        if (!empty($one->ContactNo)){
            $msg = date('dM G:i')."\nHello ".strtoupper($one->FirstName)."!\n\nPetition subject ".$subject->courseno." has been added to your enrolment list. Please be informed that the validation of petitioned subject will be after the adding and changing of subject schedule.";
            $this->sms->send($one->ContactNo, $msg);
        }

        $log = new LogController();

        $data = [
          "Description" => "Petition subject ".$subject->courseno." has been added to your enrolment list",
          "StudentNo" => $dataTMP[0],
          "AddedBy" => Auth::user()->Emp_No,
          "created_at" => date('Y-m-d h:i:s')
        ];

        $log->savelogstudent($data);

        return response()->json([
          'Error' => 0,
          'Message' => GENERAL::Success($subject->courseno. " has been added to ".$dataTMP[0])
        ]);

      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }catch(Exception $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }
    }

    public function deleterequestedsubject(Request $request){
        try{
          $id = Crypt::decryptstring($request->id);
          $sy = Crypt::decryptstring($request->sy);
          $sem = Crypt::decryptstring($request->sem);

          $g = "grades".$sy.$sem;

          if (!Schema::connection(strtolower(session('campus')))->hasTable($g))
            throw new Exception("Invalid schoolyear/semester");

          //one subject with schedule enrolled
          $one = DB::connection(strtolower(session('campus')))
              ->table($g)
              ->where("id", $id)
              ->first();

          if (empty($one))
            throw new Exception("Enrolled subject not found.");

          $del = DB::connection(strtolower(session('campus')))
            ->table("adjust")
            ->where("StudentNo", $one->StudentNo)
            ->where("CourseNo", $one->sched)
            ->where("SchoolYear", $sy)
            ->where("Semester", $sem)
            ->delete();

          if ($del){
            $one = DB::connection(strtolower(session('campus')))
            ->table($g)
            ->where("id", $id)
            ->delete();

            if ($one)
            return response()->json([
              'Error' => 0,
              'Message' => "OK"
            ]);
          }


          throw new Exception("Invalid Process");
        }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
        }catch(DecryptException $e){
            return response()->json([
              'Error' => 1,
              'Message' => $e->getMessage()
            ]);
        }
    }
}
