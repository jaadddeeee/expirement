<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\ScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Controllers\SLSU\SMSController;
use App\Http\Controllers\SLSU\RegistrationLock;

use Exception;
use Crypt;
use GENERAL;
use SCHEDULE;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Prospectos;
use App\Models\Grades;
use App\Models\DocumentsSubmitted;
use App\Models\DocumentsRequired;
use App\Models\Course;

class EnrolmentController extends Controller
{

  protected $sy;
  protected $sem;
  protected $objSched;
  public function __construct(){
      $this->objSched = new ScheduleController();
      $pref = GENERAL::getStudentDefaultEnrolment();
      if (empty($pref))
        return GENERAL::Error("Student preference not set");

      $this->sy = $pref['SchoolYear'];
      $this->sem = $pref['Semester'];
  }

  public function save(Request $request){
      try{

        $error = [];
        $ok = [];
        $selecteds = $request->input('selected');
        $regid = Crypt::decryptstring($request->input('RegistrationID'));
        $StudentNo = Crypt::decryptstring($request->input('StudentNo'));
        $maxlimit = Crypt::decryptstring($request->input('AllowedUnits'));

        if (empty($selecteds))
          throw new Exception("Nothing is selected");

        $reg = Registration::where("RegistrationID", $regid)->first();

        if (empty($reg))
          throw new Exception("No enrolment found for this student this sy/sem");

        if (!auth()->user()->AllowSuper == 1){
          if (!GENERAL::canEditEnrolment($reg->finalize)){
            throw new Exception("Student is already marked as final this sy/sem");
          }
        }


        foreach($selecteds as $selected){
            $pri = Crypt::decryptstring($selected);
            $pros = Prospectos::where("pri", $pri)->first();
            $subjname = $request->input('subjname-'.$pri) ;

            if (empty($pros)){
              array_push($error, "Invalid ID for subject <strong>".$subjname."</strong>");
            }else{
              // dd($pri);
              $selectedsched = Crypt::decryptstring($request->input('schedules-'.$pri));

              $cc = "courseoffering".$this->sy.$this->sem;
              $grades = "grades".$this->sy.$this->sem;
              if (empty($selectedsched)){
                array_push($error, "No selected schedule for the subject <strong>".$subjname."</strong>");
              }else{
                //get details of current schedule
                $schedule = DB::connection(strtolower(session('campus')))->table($cc." as cc")
                  ->select("cc.id", "cc.avail", "c.lvl", "cc.Scheme", "st1.tym as Time1",
                    "st2.tym as Time2", "t.courseno", "cc.coursecode",
                    't.coursetitle','t.units','t.exempt','c.accro','cc.max_limit')
                  ->leftjoin("course as c", "cc.course", "=", "c.id")
                  ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
                  ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
                  ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
                  ->where("cc.id", $selectedsched)
                  ->first();

                $countEnrolled = DB::connection(strtolower(session('campus')))->table($grades." as g")
                    ->where('courseofferingid', $selectedsched)
                    ->count();

                if (empty($schedule)){
                    array_push($error, "Schedule not found.");
                }elseif ($schedule->avail == 1){
                    array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule is currently close");
                }elseif((GENERAL::TotalEnrolledUnits()+$schedule->units) > $maxlimit and $schedule->exempt != 1){
                    array_push($error, "Student has reached the maximum allowable units of <strong>".$maxlimit."</strong>");
                }elseif ($countEnrolled >= $schedule->max_limit){
                    array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule has reached the maximum allowable enrollee of <strong>".$schedule->max_limit."</strong>");
                }else{
                    $hasConflict = $this->objSched->isTimeConflict(session('MySubjects'), $schedule);
                    if ($hasConflict['Conflict']){
                        $out = "";
                        foreach($hasConflict['Message'] as $sConflict){
                            if (empty($out)){
                              $out = $sConflict['CourseNo'] . " Sched ".$sConflict['Schedule'].": (".$sConflict['Time'].")";
                            }
                        }
                        array_push($error, "<strong>".$subjname."</strong> is conlfict to ".$out);
                    }else{

                        //check if naenrol na ba
                        session([
                          'msy' => $this->sy,
                          'msem' => $this->sem
                        ]);

                        $hasEnrolled =Grades::where("sched", $pros->id)
                            ->where("StudentNo", $StudentNo)
                            ->first();

                        if (!empty($hasEnrolled)){
                          array_push($error, "<strong>".$subjname."</strong> is already in your lists.");
                        }else{
                            //check if lapas cjas units
                            //check if dili pa puno

                            //e add unya sa session nga mysubjects
                            $dataSave = [
                              'gradesid' =>$regid,
                              'courseofferingid' => $schedule->id,
                              'sched' => $pros->id,
                              'StudentNo' => $StudentNo
                            ];

                            $save = DB::connection(strtolower(session('campus')))->table($grades)
                              ->insert($dataSave);
                            if ($save){
                              session()->push('MySubjects',["Time1" => $schedule->Time1, "Time2" => $schedule->Time2,
                              "CourseNo" => $subjname, 'Unit' => $schedule->units,
                              "Exempt" => $schedule->exempt]);
                              // dd(session('MySubjects'));
                              array_push($ok,[
                                'pri' => $pri,
                                'Time' => "<span class = 'text-success'><i class = 'bx bx-check'></i> (".$schedule->coursecode.") ".$schedule->Time1 ."</span>",
                                'Grade' => "<span class = 'text-info tw-bold'>0</span>"
                              ]);
                            }
                        }
                    }
                }
              }
            }
        }


        // array_push($mysubjects, ["Time1" => $mygrades['Time1'], "Time2" => $mygrades['Time2'],
        //                 "CourseNo" => $mygrades['CourseNo']]);


        // session(['MySubjects' => $mysubjects]);
        return response()->json([
            'Error' => 0,
            'ErrorRaise' => implode("<br>",$error),
            'OKRaise' => $ok,
            'Units' => GENERAL::TotalEnrolledUnits()
        ]);

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

  public function manualenrol(Request $request){
      // dd($request->hiddentStudentNo);
      try{


        $lock = new RegistrationLock([
          'sy' => $this->sy,
          'sem' => $this->sem
        ]);

        session([
          'schoolyear' => $this->sy,
          'semester' => $this->sem
        ]);

        if (!$lock->isOKToEncode())
          throw new Exception("Enrolment for ".GENERAL::setSchoolYearLabel($this->sy,$this->sem) . " ". GENERAL::Semesters()[$this->sem]['Long']. " is closed last ".date('F j, Y', strtotime($lock->getDateEnd()))." You can still process student enrollments for those who have submitted their consent.");

        $request->validate([
          'manualStudentNo' => 'required',
          'manualStudentStatus' => 'required',
          'manualStudentYear' => 'required',
          'manualStudentSection' => 'required',
        ]);

        $campus = session('campus');
        $studentno = $request->manualStudentNo;
        $studentstatus = $request->manualStudentStatus;
        $studentyear = $request->manualStudentYear;
        $section = $request->manualStudentSection;

        $student = DB::connection(strtolower($campus))
            ->table('students as s')
            ->where("StudentNo", $studentno)
            ->first();

        $course = $student->Course;
        if (empty($student)){
          throw new Exception('Student not found.');
        }

        $exReg = DB::connection(strtolower($campus))
          ->table('registration')
          ->where("StudentNo", $studentno)
          ->where("SchoolYear", $this->sy)
          ->where("Semester", $this->sem)
          ->first();

        if (!empty($exReg)){
          $course = $exReg->Course;
          if ($exReg->finalize != 2){
            throw new Exception("Student's enrolment process has started. Cannot manually enroll.");
          }
        }

        if (auth()->user()->AllowSuper != 1){
          $assignedprograms = DB::connection(strtolower(session('campus')))
          ->table("accountcourse")
          ->select("CourseID")
          ->where("UserName", strtolower(auth()->user()->Emp_No))
          ->where("CourseID", $course)
          ->first();

          if (empty($assignedprograms)){
            throw new Exception('You are not allowed to enroll students from other program.');
          }
        }


        if (empty($exReg)){

          $c = DB::connection(strtolower($campus))
              ->table('course')
              ->where('id', $student->Course)->first();

          $regid = date("YmdHis").$studentno;
          $data = [
            'RegistrationID' => $regid,
            'StudentNo' => $studentno,
            'SchoolLevel'=>$c->lvl,
            'Period' => "",
            'SchoolYear' => $this->sy,
            'Semester' => $this->sem,
            'StudentYear'=> $studentyear,
            'Section'=>$section,
            'Course'=>$student->Course,
            'Major'=>(empty($student->major)?0:$student->major),
            'UnitCost'=>0,
            'Scholar'=>0,
            'finalize'=>2,
            'StudentStatus'=>$studentstatus,
            'DateEncoded' => date('Y-m-d'),
            'TimeEncoded' => date('H:i:s'),
            'DateEnrolled' => null,
            'TimeEnrolled' => null,
            'EncodedBy' => auth()->user()->StudentNo,
            'WhereEnrolled'=>'online',
            'AdditionalIns' => '',
            'forAR' => 100
          ];
          $saveReg = DB::connection(strtolower($campus))
              ->table('registration')
              ->insert($data);

          if (!$saveReg){
            throw new Exception('Unable to manually enroll student.');
          }
        }else{

          if ($studentyear == $exReg->StudentYear and $section == $exReg->Section and $studentstatus == $exReg->StudentStatus){
            return response()->json([
              'Error' => 0,
              'Message' => Crypt::encryptstring($studentno)
            ]);
          }

          $saveReg = DB::connection(strtolower($campus))
            ->table('registration')
            ->where("StudentNo", $studentno)
            ->where("SchoolYear", $this->sy)
            ->where("Semester", $this->sem)
            ->update([
              'StudentYear' => $studentyear,
              'Section' => $section,
              'StudentStatus' => $studentstatus
            ]);

          if (!$saveReg){
            throw new Exception('Unable to manually enroll student. Student already on the list.');
          }
        }



        return response()->json([
          'Error' => 0,
          'Message' => Crypt::encryptstring($studentno)
        ]);

      }catch(Exception $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }

  }

  public function studentstatus(Request $request){
      // dd($request->hiddentStudentNo);
      try{

        $studentno = Crypt::decryptstring($request->hiddentStudentNo);
        $studentstatus = $request->StudentStatus;
        $studentyear = $request->StudentYear;
        $section = $request->StudentSection;

        if (empty($studentstatus))
          throw new Exception("Please select student status");

        if (empty($studentyear))
          throw new Exception("Please select student year.");

        if (empty($section))
          throw new Exception("Please input student section.");

        $saveReg = Registration::where("StudentNo", $studentno)
          ->where("SchoolYear", $this->sy)
          ->where("Semester", $this->sem)
          ->update([
            'StudentYear' => $studentyear,
            'Section' => $section,
            'StudentStatus' => $studentstatus
          ]);

        return response()->json([
          'Error' => 0,
          'Message' =>$request->hiddentStudentNo
        ]);
      }catch(Exception $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
        ]);
      }

  }

  public function cart(Request $request){
    try{
      $id = Crypt::decryptstring($request->id);

      $one = Student::where("StudentNo", $id)->first();

      if (empty($one))
        throw new Exception("Invalid Student");

      $reg = Registration::where("StudentNo", $id)
        ->where("SchoolYear", $this->sy)
        ->where("Semester", $this->sem)
        ->first();

      session([
        'schoolyear' => $this->sy,
        'semester' => $this->sem
      ]);

      $tbl = "grades".$this->sy.$this->sem;
      $cc = "courseoffering".$this->sy.$this->sem;
      $subjects = DB::connection(strtolower(session('campus')))
          ->table($tbl." as g")
          ->select("g.*", "t.courseno", "t.coursetitle", "t.units", "t.exempt",
            "st1.tym as Time1", "st2.tym as Time2", "cc.coursecode",'cc.RequireReqForm')
          ->leftjoin("transcript as t", "g.sched", "=", "t.id")
          ->leftjoin($cc." as cc", "g.courseofferingid", "=", "cc.id")
          ->leftjoin("schedule_time as st1", "cc.sched", "=", "st1.id")
          ->leftjoin("schedule_time as st2", "cc.sched2", "=", "st2.id")
          ->where("StudentNo", $reg->StudentNo)
          ->orderby('t.sort_order')
          ->get();
      $mUnits = 0;

      if (!empty($reg)){
        $toCheck = $reg->Course;
        if (!empty($reg->Major))
          $toCheck = $reg->Major;

        $mUnits = GENERAL::getMaxUnit([
          'Course' => $toCheck,
          'StudentYear' => $reg->StudentYear,
          'Semester' => $reg->Semester,
          'StudentNo' => $reg->StudentNo,
          'SchoolYear' => $reg->SchoolYear,
        ]);
      }


      return view("slsu.enrol.cart",compact('one','reg','subjects'),[
        'MaxLimit' => $mUnits,
        'TotalEnrolled' => GENERAL::TotalEnrolledUnits()
      ]);

    }catch(DecryptException $e){
      return GENERAL::Error($e->getMessage());
    }
  }

  public function deletecart(Request $request){
      try{

        $id = Crypt::decryptstring($request->id);
        $snum = Crypt::decryptstring($request->snum);

        $reg = Registration::where("StudentNo", $snum)
          ->where("SchoolYear", $this->sy)
          ->where("Semester", $this->sem)
          ->first();

        if (empty($reg))
          throw new Exception("Enrollment not found.");

        if (!GENERAL::canEditEnrolment($reg->finalize))
          throw new Exception("Cannot delete subject.");

        $tbl = "grades".$this->sy.$this->sem;
        $getSub = DB::connection(strtolower(session('campus')))
            ->table($tbl." as g")
            ->select("t.exempt", "t.units", "t.courseno", "t.pri")
            ->leftjoin("transcript as t", "g.sched", "=", "t.id")
            ->where("g.id", $id)
            ->where("g.StudentNo", $snum)
            ->first();

        if (empty($getSub))
          throw new Exception("Invalid Subject. Enrolled subject not found");

        $deleteSub = DB::connection(strtolower(session('campus')))
          ->table($tbl)
          ->where("id", $id)
          ->where("StudentNo", $snum)
          ->delete();

        if (!$deleteSub)
          throw new Exception("Unable to delete subject");

        $this->removeEnrolledSubject($snum);
        SCHEDULE::setId($getSub->pri);
        return response()->json([
            'Error' => 0,
            'Message' => GENERAL::TotalEnrolledUnits(),
            'Schedule' => SCHEDULE::ListsToCMB($reg->StudentYear,$reg->Section),
            'pri' => $getSub->pri
        ]);

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

  public function removeEnrolledSubject($snum = ""){

    $mysubjects = [];
    $reg = Registration::where("StudentNo", $snum)
      ->where("SchoolYear", $this->sy)
      ->where("Semester", $this->sem)
      ->first();

      $blgrades = "grades".$this->sy.$this->sem;
      $tblCC = "courseoffering".$this->sy.$this->sem;

      if (Schema::connection(strtolower(session('campus')))->hasTable($blgrades)) {
          $out = DB::connection(strtolower(session('campus')))->table($blgrades." as g")
              ->select('g.gradesid', 'g.inc', 'g.final', 'g.sched', 'g.StudentNo','g.courseofferingid',
                      "cc.coursecode","st1.tym as Time1","st2.tym as Time2",
                      "t.courseno","t.units", "t.exempt")
              ->leftjoin($tblCC." as cc", "g.courseofferingid","=", "cc.id")
              ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
              ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
              ->leftjoin("transcript as t", "g.sched", "=", "t.id")
              ->where("g.StudentNo", $snum)
              ->get();

          foreach ($out as $mygrades){
            array_push($mysubjects, [
              "Time1" => $mygrades->Time1,
              "Time2" => $mygrades->Time2,
              "CourseNo" => $mygrades->courseno,
              "Unit" => $mygrades->units,
              "Exempt" => $mygrades->exempt]);
          }
      }

      session(['MySubjects' => $mysubjects]);

  }

  public function finalize(Request $request){
      try{
        $snum = Crypt::decryptstring($request->snum);
        if (empty($snum))
          throw new Exception("Invalid Student");

          $one = Student::where("StudentNo", $snum)->first();
          if (empty($one))
            throw new Exception("Student not found.");

          $reg = Registration::where("StudentNo", $snum)
            ->where("SchoolYear", $this->sy)
            ->where("Semester", $this->sem)
            ->first();

          if (empty($reg))
            throw new Exception("Student has no record this sy/sem.");

          if (!GENERAL::canEditEnrolment($reg->finalize))
            throw new Exception("Student has been marked as finalized already.");


          $enrolledUnits = GENERAL::TotalEnrolledUnits();

          $toCheck = $reg->Course;
          if (!empty($reg->Major))
            $toCheck = $reg->Major;

          $maxlimit = GENERAL::getMaxUnit([
            'Course' => $toCheck,
            'StudentYear' => $reg->StudentYear,
            'Semester' => $reg->Semester,
            'StudentNo' => $reg->StudentNo,
            'SchoolYear' => $reg->SchoolYear,
          ]);

          if ($enrolledUnits > $maxlimit)
            throw new Exception("Student has reached the maximum allowed units of ".$maxlimit);

          $tbl = "grades".$this->sy.$this->sem;
          $subjects = DB::connection(strtolower(session('campus')))
                ->table($tbl." as g")
                ->select("g.*")
                ->where("StudentNo", $snum)
                ->get();

          if (count($subjects) <= 0)
              throw new Exception("Student has no subject(s) enrolled.");

          $fin = 0;

          // if ($reg->TES == 2)
          //   $fin = 4;

          $data = [
            'finalize' => $fin,
            'TES' => 0,
            'EnrollingOfficer' => auth()->user()->UserName,
            'DateEnrolled' => date('Y-m-d'),
            'TimeEnrolled' => date('h:i:s')
          ];

          $upreg = Registration::where("StudentNo", $snum)
            ->where("SchoolYear", $this->sy)
            ->where("Semester", $this->sem)
            ->update($data);

          if (!$upreg)
            throw new Exception("Unable to finalize the student's enrolment");

          return response()->json([
              'Error' => 0,
              'Message' => ''
          ]);
      }catch(Exception $e){
        return response()->json([
            'Error' => 1,
            'Message' => GENERAL::Error($e->getMessage())
        ]);
      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => GENERAL::Error($e->getMessage())
      ]);
      }
  }


  //Registrar
  public function regenrolment(){
    $pageTitle = "Manage Step 5";
    $headerAction = '<a href="#" class="reglistsearch btn btn-sm btn-primary" role="button"><span class="mdi mdi-database-refresh-outline"></span> Refresh</a>';

    try{

      $courses = Course::select('course_title as name','id as abbreviation', 'accro')
        ->where('isActive', 0)
        ->orderby('accro')->get();


      return view('slsu.enrol.regselectcourse', compact('courses'), [
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

  public function regenrolmentlist(Request $request){
    try{

      $step1q = Registration::query();
      $step1q->where("finalize", 5)
          ->select("registration.*", "LastName", "FirstName", "accro", "course_major")
          ->leftjoin("students as s", "registration.StudentNo", "=", "s.StudentNo")
          ->leftjoin("course as c", "registration.Course", "=", "c.id")
          ->leftjoin("major as m", "registration.Major", "=", "m.id")
          ->where("registration.SchoolYear", $this->sy)
          ->where("registration.Semester", $this->sem);
      if (!empty($request->courses)){
        $step1q->whereIn('registration.Course', $request->courses);
      }
      $step1q->orderby("DateEnrolled")
          ->orderby("TimeEnrolled");

      $step1s = $step1q->get();
      return view('slsu.enrol.reglist', compact('step1s'));
    }catch(Exception $e){
      return response()->json([
        'errors' => $e->getMessage(),
      ], 400);
    }
  }

  public function validateenrolment(Request $request){
    try{

      $id = Crypt::decryptstring($request->id);

      $one = Student::where("StudentNo", $id)->first();

      if (empty($one))
        throw new Exception("Invalid Student");

      $parSchoolYear = $this->sy;
      $parSemester = $this->sem;
      if (isset($request->SchoolYear)){
        $parSchoolYear = Crypt::decryptstring($request->SchoolYear);
      }

      if (isset($request->Semester)){
        $parSemester = Crypt::decryptstring($request->Semester);
      }
      // dd($parSemester);

      session([
        'schoolyear' => $parSchoolYear,
        'semester' => $parSemester
      ]);

      $reg = Registration::where("StudentNo", $id)
        ->where("SchoolYear", $parSchoolYear)
        ->where("Semester", $parSemester)
        ->first();


      if (empty($reg)){
        throw new Exception("Student not enrolled on the selected preference.");
      }

      $ids = [];
      if (!empty($reg->subjects)){

        foreach($reg->subjects as $sbs){
          $ids[] = $sbs->sched;
        }
      }
      $cur_num = $one->cur_num;
      if (!empty($reg->cur_num)){
        $cur_num = $reg->cur_num;
      }
      $tbl = "grades".$parSchoolYear.$parSemester;
      $cc = "courseoffering".$parSchoolYear.$parSemester;
      $subjects = DB::connection(strtolower(session('campus')))
          ->table($tbl." as g")
          ->select("g.*", "t.courseno", "t.coursetitle", "t.units", "t.exempt", "t.pri",
            "st1.tym as Time1", "st2.tym as Time2", "cc.coursecode", "cc.RequireReqForm", 'e.LastName', 'e.FirstName')
          ->leftjoin("transcript as t", "g.sched", "=", "t.id")
          ->leftjoin($cc." as cc", "g.courseofferingid", "=", "cc.id")
          ->leftjoin("schedule_time as st1", "cc.sched", "=", "st1.id")
          ->leftjoin("schedule_time as st2", "cc.sched2", "=", "st2.id")
          ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
          ->where("StudentNo", $reg->StudentNo)
          ->orderby('t.sort_order')
          ->get();
      $mUnits = 0;

      if (!empty($reg)){
        $toCheck = $reg->Course;
        if (!empty($reg->Major))
          $toCheck = $reg->Major;

        $mUnits = GENERAL::getMaxUnit([
          'Course' => $toCheck,
          'StudentYear' => $reg->StudentYear,
          'Semester' => $reg->Semester,
          'StudentNo' => $reg->StudentNo,
          'SchoolYear' => $reg->SchoolYear,
        ]);
      }
      $submitted = [];
      $required = [];
      $petition = [];
      // dd($reg->StudentStatus);
      // $submitted = DocumentsSubmitted::where("StudentNo", $reg->StudentNo)->get();
      // $required = DocumentsRequired::where("StudentStatus", ($one->isALS==1?"ALS":$reg->StudentStatus))->get();
      // $petition = DocumentsRequired::where("StudentStatus", "REQ")->first();

      // dd($ids);
      $subtmp = Prospectos::query();
      $subtmp->where('cur_num', $cur_num)
        ->where('course',$reg->Course)
        ->where('hide','<>',1);
      $major = $reg->Major;
      if (!empty($reg->Major)){
        $subtmp->where(function($q) use($major){
          $q->orwhere('major_in',$major)
            ->orWhere('major_in',0);
        });
      }
      $subtmp->whereNotIn('id', $ids);
      $subtmp->orderby('stud_year')
        ->orderby('semester')
        ->orderby('courseno');
      $addsubjects = $subtmp->get();

      // dump($addsubjects);
      $pageTitle = $one->StudentNo. " " .$one->FirstName.' '.$one->LastName;
      $headerAction = '<a href="#" class="reglistsearch btn btn-sm btn-primary" role="button">Refresh</a>';

      return view("slsu.enrol.forvalidation",compact('one','reg','subjects','submitted','required','petition','addsubjects'),[
        'MaxLimit' => $mUnits,
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'CurNum' => $cur_num
      ]);

    }catch(DecryptException $e){
      return view('slsu.error.all', [
        'pageTitle' => "Error Processing",
        'Error' => $e->getMessage()
      ]);
    }
  }

  public function validatepro(Request $request){
    try{

      $id = Crypt::decryptstring($request->hidStudentNo);

      $one = Student::where("StudentNo", $id)->first();

      if (empty($one))
        throw new Exception("Invalid Student");

      $reg = Registration::where("StudentNo", $id)
        ->where("SchoolYear", $this->sy)
        ->where("Semester", $this->sem)
        ->first();

      if (empty($reg))
        throw new Exception("No enrollment record found on the current sy/sem");

      if ($reg->finalize == 1)
        throw new Exception("The student was marked received (validated) already");

      if ($reg->finalize != 5)
        throw new Exception("It seems you might have missed a step in the process.");

      $tbl = "grades".$this->sy.$this->sem;
      $subjects = DB::connection(strtolower(session('campus')))
          ->table($tbl." as g")
          ->where("g.StudentNo", $reg->StudentNo)
          ->get();

      if (count($subjects) <= 0)
        throw new Exception("You have no enrolled subject(s)");

      // if (empty($docrequired))
      //   throw new Exception("All required documents must be submitted first.");

      // if (count($docrequired) != $hidCtr)
      //   throw new Exception("All required documents must be submitted first.");

      $data = [
        'finalize' => 1,
        'DateValidated' => date('Y-m-d'),
        'ValidatedBy' => auth()->user()->UserName,
        'TimeValidated' => date('h:i:s')
      ];

      $upreg = Registration::where("StudentNo", $id)
        ->where("SchoolYear", $this->sy)
        ->where("Semester", $this->sem)
        ->update($data);
      if ($upreg){
        $sms = new SMSController();
        if (!empty($one->ContactNo)){
          $des = "Hi ".utf8_decode($one->FirstName)."!\nCongratulations! You are now officially enrolled. Log in to the system to have a copy of your ORF.";
          $ret = $sms->send($one->ContactNo, $des);
          if (!empty($ret)){
            if ($ret['Error'] == 1){
              throw new Exception("Student was validated but unable to be notified via SMS. Reason: ".$ret['Message']);
            }
          }
        }
      }
      return response()->json([
        'errors' => "OK"],200);
    }catch(Exception $e){
      return response()->json([
        'errors' => "Line ".$e->getLine().": ".$e->getMessage()
      ],400);
    }catch(DecryptException $e){
      return response()->json([
        'errors' => "Line ".$e->getLine().": ".$e->getMessage()
      ],400);
    }
  }

  public function searchenrollee(Request $request){
    try{

      $request->validate([
        'str' => 'required',
        'SchoolYear' => 'required',
        'Semester' => 'required',
      ]);

      $tmp = explode(' - ', $request->str);
      if (sizeof($tmp) == 1)
        $studentno = $request->str;
      else
        $studentno = $tmp[0];

      $one = Student::where("StudentNo", $studentno)->first();
      if (empty($one))
        throw new Exception("Student not found.");

      $reg = Registration::where('StudentNo', $studentno)
        ->where("SchoolYear", $request->SchoolYear)
        ->where("Semester", $request->Semester)
        ->first();
      // dd($studentno);
      if (empty($reg)){
        throw new Exception("No enrollment record for the preferences selected.");
      }

      return response()->json([ 'url' =>
        route('validate',
        [
          'id' => Crypt::encryptstring($studentno),
          'SchoolYear' => Crypt::encryptstring($request->SchoolYear),
          'Semester'=>Crypt::encryptstring($request->Semester)
        ])
      ],200);
    }catch(Exception $e){
      return response()->json([
        'errors' => GENERAL::Error("Line ".$e->getLine().": ".$e->getMessage())
      ],400);
    }
  }

  public function withdraw(Request $request){
    try{
      $id = Crypt::decryptstring($request->regid);
      if (empty($id)){
        throw new Exception('Invalid ID');
      }

      $reg = Registration::where("RegistrationID", $id)->first();
      if (empty($reg)){
        throw new Exception("Student is not enrolled.");
      }

      $grades = "grades".$reg->SchoolYear.$reg->Semester;
      $subjects = DB::connection(strtolower(session('campus')))
      ->table($grades)
      ->where("StudentNo", $reg->StudentNo)
      ->delete();

      if (!$reg->delete()){
        throw new Exception("Unable to withdraw student.");
      }

      $log = new LogController();
      $data = [
        "Description" => "Your enrolment has been withdrawn (".$reg->SchoolYear.'-'.$reg->Semester.")",
        "StudentNo" => $reg->StudentNo,
        "AddedBy" => Auth::user()->Emp_No,
        "created_at" => date('Y-m-d h:i:s')
      ];

      $log->savelogstudent($data);

      $data = [
          "CourseNo" => "",
          "AddedBy" => Auth::user()->Emp_No,
          "DateAdded" => date('Y-m-d'),
          "ActionDone" => "Withdraw",
          "StudentNo" => $reg->StudentNo,
          "SchoolYear" => $reg->SchoolYear,
          "Semester" => $reg->Semester,
          "Reason" => ''
        ];

      $log->saveaction($data);

    }catch(Exception $e){
        return response()->json([
            'errors' => $e->getMessage()
        ],400);
    }catch(DecryptException $e){
      return response()->json([
        'errors' => $e->getMessage()
    ],400);
    }
  }

  public function popuschedule(Request $request){
    try{
      if (empty($request->id)){
        throw new Exception('Invalid ID');
      }

      SCHEDULE::setId($request->id);
      SCHEDULE::setSy($request->sy);
      SCHEDULE::setSem($request->sem);

      return SCHEDULE::ListsToCMB(0,0,'');
    }catch(Exception $e){
      return response()->json([
          'errors' => $e->getMessage()
      ],400);
    }
  }

  public function promodifysubject(Request $request){
    try{

      $regid = Crypt::decryptstring($request->hidRegistrationID);
      $subpri = $request->ModifySubjects;
      $sch = 'schedules-'.$subpri;
      if (!empty($request->$sch)){
        $schedid = Crypt::decryptstring($request->$sch);
      }
      $ReasonModify = $request->ReasonModify;
      $ModifyCourseCode = $request->ModifyCourseCode;


      if (empty($regid)){
        throw new Exception('Invalid ID.');
      }


      if (empty($subpri)){
        throw new Exception('No subject selected.');
      }

      if (empty($ReasonModify)){
        throw new Exception('No reason for changing.');
      }

      if (!empty($schedid) and !empty($ModifyCourseCode)){
        throw new Exception('You cannot select both drop down schedule and course code.');
      }

      if (empty($schedid) and empty($ModifyCourseCode)){
        throw new Exception('Please select schedule from the dropdown or enter course code');
      }

      $reg = Registration::where("RegistrationID", $regid)->first();
      if (empty($reg)){
        throw new Exception('Student\'s enrollment not found.');
      }


      $subject = Prospectos::where('pri', $subpri)->first();
      if (empty($subject)){
        throw new Exception('Subject not found.');
      }

      $oldsched = "";
      if (!empty($reg->subjects)){
        foreach($reg->subjects as $onesubj){
            if ($onesubj->sched == $subject->id){
              $oldsched = $onesubj->courseofferingid;
            }
        }
      }

      $grades = GENERAL::CreateTMPGrades(['StudentNo' => $reg->StudentNo]);

      $TotalEnrolledUnits = 0;
      $mysubjects = [];
      foreach($grades as $mygrades){
          if ($mygrades['SchoolYear'] == $reg->SchoolYear and $mygrades['Semester'] == $reg->Semester){
              if ($mygrades['Exempt'] != 1){
                $TotalEnrolledUnits += $mygrades['Unit'];
              }

              array_push($mysubjects, [
                "Time1" => $mygrades['Time1'],
                "Time2" => $mygrades['Time2'],
                "CourseNo" => $mygrades['CourseNo'],
                "Unit" => $mygrades['Unit'],
                "Exempt" => $mygrades['Exempt']
              ]);

          }
      }

      // if (!GENERAL::isCleared($reg->StudentNo, ['SY' => $this->sy, 'SEM' => $this->sem])){
      //   throw new Exception("Student is not yet cleared from his/her obligations.");
      // }

      //start sa sched checking
      $error = [];
      $cc = "courseoffering".$reg->SchoolYear.$reg->Semester;
      $grades = "grades".$reg->SchoolYear.$reg->Semester;


      //get details of current schedule
      if (!empty($ModifyCourseCode)){
        $schedule = DB::connection(strtolower(session('campus')))->table($cc." as cc")
        ->select("cc.id", "cc.avail", "c.lvl", "cc.Scheme", "st1.tym as Time1",
          "st2.tym as Time2", "t.courseno", "cc.coursecode",
          't.coursetitle','t.units','t.exempt','c.accro','cc.max_limit')
        ->leftjoin("course as c", "cc.course", "=", "c.id")
        ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
        ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
        ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
        ->where("cc.coursecode", $ModifyCourseCode)
        ->first();
      }else{
        $schedule = DB::connection(strtolower(session('campus')))->table($cc." as cc")
        ->select("cc.id", "cc.avail", "c.lvl", "cc.Scheme", "st1.tym as Time1",
          "st2.tym as Time2", "t.courseno", "cc.coursecode",
          't.coursetitle','t.units','t.exempt','c.accro','cc.max_limit')
        ->leftjoin("course as c", "cc.course", "=", "c.id")
        ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
        ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
        ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
        ->where("cc.id", $schedid)
        ->first();
      }

      if ($oldsched == $schedule->id){
        throw new Exception("No changes from the current schedule.");
      }
      // dump($schedule);
      $countEnrolled = DB::connection(strtolower(session('campus')))->table($grades." as g")
          ->where('courseofferingid', $schedule->id)
          ->count();

      if (empty($schedule)){
          array_push($error, "Schedule not found.");
      }
      if ($schedule->avail == 1){
          array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule is currently close");
      }

      if ($countEnrolled >= $schedule->max_limit){
          array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule has reached the maximum allowable enrollee of <strong>".$schedule->max_limit."</strong>");
      }

      $hasConflict = $this->objSched->isTimeConflict($mysubjects, $schedule);
      if ($hasConflict['Conflict']){
          $out = "";
          foreach($hasConflict['Message'] as $sConflict){
              if (empty($out)){
                $out = $sConflict['CourseNo'] . " Sched ".$sConflict['Schedule'].": (".$sConflict['Time'].")";
              }
          }
          array_push($error, "<strong>".$subject->courseno."</strong> is conlfict to ".$out);
      }

      if (!empty($error)){
        throw new Exception(implode("<br>", $error));
      }
      //e add unya sa session nga mysubjects
      $dataSave = [
        'courseofferingid' => $schedule->id,
      ];

      $save = DB::connection(strtolower(session('campus')))->table($grades)
        ->where('sched', $subject->id)
        ->where("gradesid", $regid)
        ->update($dataSave);
      if (!$save){
        throw new Exception('Unable to modify schedule.');
      }
      $log = new LogController();
      $data = [
        "CourseNo" => $subject->id,
        "AddedBy" => Auth::user()->Emp_No,
        "DateAdded" => date('Y-m-d'),
        "ActionDone" => "Change",
        "StudentNo" => $reg->StudentNo,
        "SchoolYear" => $reg->SchoolYear,
        "Semester" => $reg->Semester,
        "Reason" => $ReasonModify,
        'CourseNoString' => $subject->courseno
      ];

    $log->saveaction($data);
      return GENERAL::Success($subject->courseno .'\'s schedule with course code '.$schedule->coursecode.' has been successfully change. Page will refresh is 1s');
    }catch(Exception $e){
      return response()->json([
          'errors' => GENERAL::Error('LINE '. $e->getLine().': '.$e->getMessage())
      ],400);
    }catch(DecryptException $e){
      return response()->json([
        'errors' => GENERAL::Error($e->getMessage())
    ],400);
    }
  }

  public function proaddsubject(Request $request){
    try{

      $regid = Crypt::decryptstring($request->hidRegistrationID);
      $subpri = $request->AddSubjects;
      $sch = 'schedules-'.$subpri;
      if (!empty($request->$sch)){
        $schedid = Crypt::decryptstring($request->$sch);
      }
      $ReasonAdd = $request->ReasonAdd;
      $AddCourseCode = $request->AddCourseCode;


      if (empty($regid)){
        throw new Exception('Invalid ID.');
      }


      if (empty($subpri)){
        throw new Exception('No subject selected.');
      }

      if (empty($ReasonAdd)){
        throw new Exception('No reason for adding.');
      }

      if (!empty($schedid) and !empty($AddCourseCode)){
        throw new Exception('You cannot select both drop down schedule and course code.');
      }

      if (empty($schedid) and empty($AddCourseCode)){
        throw new Exception('Please select schedule from the dropdown or enter course code');
      }

      $reg = Registration::where("RegistrationID", $regid)->first();
      if (empty($reg)){
        throw new Exception('Student\'s enrollment not found.');
      }

      $subject = Prospectos::where('pri', $subpri)->first();
      if (empty($subject)){
        throw new Exception('Subject not found.');
      }

      if (!empty($reg->subjects)){
        foreach($reg->subjects as $onesubj){
            if ($onesubj->sched == $subject->id){
              throw new Exception('Subject is already in your list.');
            }
        }
      }

      $credits = GENERAL::getCreditedSubjects(['StudentNo' => $reg->StudentNo]);
      $grades = GENERAL::CreateTMPGrades(['StudentNo' => $reg->StudentNo]);

      $TotalEnrolledUnits = 0;
      $mysubjects = [];
      foreach($grades as $mygrades){
          if ($mygrades['SchoolYear'] == $reg->SchoolYear and $mygrades['Semester'] == $reg->Semester){
              if ($mygrades['Exempt'] != 1){
                $TotalEnrolledUnits += $mygrades['Unit'];
              }

              array_push($mysubjects, [
                "Time1" => $mygrades['Time1'],
                "Time2" => $mygrades['Time2'],
                "CourseNo" => $mygrades['CourseNo'],
                "Unit" => $mygrades['Unit'],
                "Exempt" => $mygrades['Exempt']
              ]);

          }
      }

      $isC = GENERAL::isCredited($credits, $subject->id);
      if ($isC){
        throw new Exception('Credited, no need to add.');
      }

      if (!empty($subject->prerequisite)){
        $tmpPre = explode(",", $subject->prerequisite);
        $ctr = 0;
        foreach ($tmpPre as $tmpPrevalue) {
            $tmpP = GENERAL::getGradesinTMP($tmpPrevalue,$grades);
            $Preinc = (isset($tmpP['inc'])?$tmpP['inc']:"");
            $Prefinal = (isset($tmpP['final'])?$tmpP['final']:"");

            $tmpStatus = GENERAL::isGradePass($Prefinal,$Preinc);

            if ($tmpStatus != "passed"){
                $cre = GENERAL::isCredited($credits, $tmpPrevalue);
                if (!$cre){
                    $ctr++;
                }

            }
        }

        if ($ctr > 0){
          throw new Exception('Problem with pre-requisite');
        }
      }

      // if (!GENERAL::isCleared($reg->StudentNo, ['SY' => $this->sy, 'SEM' => $this->sem])){
      //   throw new Exception("Student is not yet cleared from his/her obligations.");
      // }

      //start sa sched checking
      $error = [];
      $cc = "courseoffering".$reg->SchoolYear.$reg->Semester;
      $grades = "grades".$reg->SchoolYear.$reg->Semester;

      $toCheck = $reg->Course;
      if (!empty($reg->Major) and $reg->Major != 1){
        $toCheck = $reg->Major;
      }
      $maxlimit = GENERAL::getMaxUnit([
        'Course' => $toCheck,
        'StudentYear' => $reg->StudentYear,
        'Semester' => $reg->Semester,
        'StudentNo' => $reg->StudentNo,
        'SchoolYear' => $reg->SchoolYear,
      ]);

      //get details of current schedule
      if (!empty($AddCourseCode)){
        $schedule = DB::connection(strtolower(session('campus')))->table($cc." as cc")
        ->select("cc.id", "cc.avail", "c.lvl", "cc.Scheme", "st1.tym as Time1",
          "st2.tym as Time2", "t.courseno", "cc.coursecode",
          't.coursetitle','t.units','t.exempt','c.accro','cc.max_limit','cc.RequireReqForm')
        ->leftjoin("course as c", "cc.course", "=", "c.id")
        ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
        ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
        ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
        ->where("cc.coursecode", $AddCourseCode)
        ->first();
      }else{
        $schedule = DB::connection(strtolower(session('campus')))->table($cc." as cc")
        ->select("cc.id", "cc.avail", "c.lvl", "cc.Scheme", "st1.tym as Time1",
          "st2.tym as Time2", "t.courseno", "cc.coursecode",
          't.coursetitle','t.units','t.exempt','c.accro','cc.max_limit','cc.RequireReqForm')
        ->leftjoin("course as c", "cc.course", "=", "c.id")
        ->leftjoin("schedule_time as st1", "cc.sched","=", "st1.id")
        ->leftjoin("schedule_time as st2", "cc.sched2","=", "st2.id")
        ->leftjoin("transcript as t", "cc.courseid", "=", "t.id")
        ->where("cc.id", $schedid)
        ->first();
      }

      // dump($schedule);
      $countEnrolled = DB::connection(strtolower(session('campus')))->table($grades." as g")
          ->where('courseofferingid', $schedule->id)
          ->count();

      if (empty($schedule)){
          array_push($error, "Schedule not found.");
      }
      if ($schedule->avail == 1){
          array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule is currently close");
      }
      if(($TotalEnrolledUnits+$schedule->units) > $maxlimit and $schedule->exempt != 1){

          array_push($error, "Student has reached the maximum allowable units of <strong>".$maxlimit."</strong>");
      }
      if ($countEnrolled >= $schedule->max_limit){
          array_push($error, $schedule->accro.' : ' .$schedule->coursecode ."'s schedule has reached the maximum allowable enrollee of <strong>".$schedule->max_limit."</strong>");
      }

      if ($schedule->RequireReqForm == 1){
        array_push($error, "Schedule is a requested subject. Only UISA/CISA can add.");
      }

      $hasConflict = $this->objSched->isTimeConflict($mysubjects, $schedule);
      if ($hasConflict['Conflict']){
          $out = "";
          foreach($hasConflict['Message'] as $sConflict){
              if (empty($out)){
                $out = $sConflict['CourseNo'] . " Sched ".$sConflict['Schedule'].": (".$sConflict['Time'].")";
              }
          }
          array_push($error, "<strong>".$subject->courseno."</strong> is conlfict to ".$out);
      }

      if (!empty($error)){
        throw new Exception(implode("<br>", $error));
      }
      //e add unya sa session nga mysubjects
      $dataSave = [
        'gradesid' =>$reg->RegistrationID,
        'courseofferingid' => $schedule->id,
        'sched' => $subject->id,
        'StudentNo' => $reg->StudentNo
      ];

      $save = DB::connection(strtolower(session('campus')))->table($grades)
        ->insert($dataSave);
      if (!$save){
        throw new Exception('Unable to add subject.');
      }
      $log = new LogController();
      $data = [
        "CourseNo" => $subject->id,
        "AddedBy" => Auth::user()->Emp_No,
        "DateAdded" => date('Y-m-d'),
        "ActionDone" => "Add",
        "StudentNo" => $reg->StudentNo,
        "SchoolYear" => $reg->SchoolYear,
        "Semester" => $reg->Semester,
        "Reason" => $ReasonAdd,
        'CourseNoString' => $subject->courseno
      ];

      $log->saveaction($data);

      return GENERAL::Success($subject->courseno .' with course code '.$schedule->coursecode.' has been successfully added. Page will refresh is 1s');
    }catch(Exception $e){
      return response()->json([
          'errors' => GENERAL::Error('LINE '. $e->getLine().': '.$e->getMessage())
      ],400);
    }catch(DecryptException $e){
      return response()->json([
        'errors' => GENERAL::Error($e->getMessage())
    ],400);
    }
  }

  public function prodropsubject(Request $request){
    try{

      $regid = Crypt::decryptstring($request->hidRegistrationID);
      $subid = Crypt::decryptstring($request->DropSubject);
      $ReasonDrop = $request->ReasonDrop;
      // dd($request->all());
      if (empty($regid)){
        throw new Exception('Invalid ID.');
      }

      if (empty($subid)){
        throw new Exception('No subject selected.');
      }

      if (empty($ReasonDrop)){
        throw new Exception('No reason for adding.');
      }

      $reg = Registration::where("RegistrationID", $regid)->first();
      if (empty($reg)){
        throw new Exception('Student\'s enrollment not found.');
      }

      // $found = false;
      // $subid_sched = 0;
      // if (!empty($reg->subjects)){
      //   foreach($reg->subjects as $onesubj){
      //       if ($onesubj->id == $subid){
      //         $found = true;
      //         $subid_sched = $onesubj->sched;
      //       }
      //   }
      // }
      $tbl = 'grades'.$reg->SchoolYear.$reg->Semester;
      $oneenrolled = DB::connection(strtolower(session('campus')))
        ->table($tbl)
        ->where('id', $subid)
        ->first();

      if (empty($oneenrolled)){
        throw new Exception('Subject is not in student\'s lists.');
      }

      $subject = Prospectos::where('id', $oneenrolled->sched)->first();
      if (empty($subject)){
        throw new Exception('Subject not found.');
      }

      $onedelete = DB::connection(strtolower(session('campus')))
      ->table($tbl)
      ->where('id', $subid)
      ->delete();

      if (!$onedelete){
        throw new Exception('Unable to delete subject.');
      }

      $log = new LogController();
      $data = [
        "CourseNo" => $subject->id,
        "AddedBy" => Auth::user()->Emp_No,
        "DateAdded" => date('Y-m-d'),
        "ActionDone" => "Drop",
        "StudentNo" => $reg->StudentNo,
        "SchoolYear" => $reg->SchoolYear,
        "Semester" => $reg->Semester,
        "Reason" => $ReasonDrop,
        'CourseNoString' => $subject->courseno
      ];

      $log->saveaction($data);

      return GENERAL::Success($subject->courseno .' has been successfully dropped. Page will refresh is 1s');
    }catch(Exception $e){
      return response()->json([
          'errors' => GENERAL::Error('LINE '. $e->getLine().': '.$e->getMessage())
      ],400);
    }catch(DecryptException $e){
      return response()->json([
        'errors' => GENERAL::Error($e->getMessage())
    ],400);
    }
  }
}
