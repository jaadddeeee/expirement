<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\SLSU\SMSController;

use Exception;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\Registration;
use GENERAL;

class TESController extends Controller
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

    public function index(){

      $pageTitle = "Manage STEP 2 - FHE";
      $headerAction = '<a href="/tes" class="btn btn-sm btn-primary" role="button">Refresh</a>';

      $registrations = DB::connection(strtolower(session('campus')))->table('registration as r')
        ->select('r.*', 'c.accro', 'm.course_major','s.LastName','s.FirstName')
        ->leftjoin("course as c", "r.Course", "=", "c.id")
        ->leftjoin("major as m", "r.Major", "m.id")
        ->leftjoin('students as s', 'r.StudentNo', '=', 's.StudentNo')
        ->where("r.TES",0)
        ->where("r.finalize",0)
        ->where("r.SchoolLevel","Under Graduate")
        ->where("r.SchoolYear", $this->sy)
        ->where("r.Semester", $this->sem)
        ->orderBy("DateEnrolled", "ASC")
        ->orderBy("TimeEnrolled", "ASC")
        ->paginate(30);

      return view('slsu.tes.index',compact('registrations'),[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
      ]);
    }

    public function search(Request $request){
        try{
            if ($request->ajax()){
              $str = $request->str;

              if (empty($str))
                throw new Exception("Empty Search String");

                $dataTMP = explode(" - ", $str);

              if (sizeof($dataTMP) == 1){
                $studentnumber = $str;
              }else{
                $studentnumber = $dataTMP[0];
              }
            }else{
              $studentnumber = Crypt::decryptstring($request->id);
            }


              $tmpStudent =  DB::connection(strtolower(session('campus')))
                ->table('students as s')
                ->select("s.*", "c.accro", "m.course_major")
                ->leftjoin("course as c", "s.Course", "=", "c.id")
                ->leftjoin("major as m", "s.major", "m.id")
                ->where("s.StudentNo", $studentnumber)
                ->first();

              if (empty($tmpStudent))
                throw new Exception("Student not found. ");

              $registrations = DB::connection(strtolower(session('campus')))->table('registration as r')
                  ->leftjoin('scholar as s', 'r.Scholar', '=', 's.id')
                  ->leftjoin('studentlevel as sl', 'r.SchoolLevel', '=','sl.Description')
                  ->leftjoin("course as c", "r.Course", "=", "c.id")
                  ->leftjoin("major as m", "r.Major", "m.id")
                  ->where("StudentNo", $studentnumber)
                  ->where("r.TES","<>",0)
                  ->orderBy("SchoolYear", "DESC")
                  ->orderBy("Semester", "DESC")
                  ->get();

            if ($request->ajax()){
              return view("slsu.tes.list-one-student", compact('registrations','tmpStudent'));
            }else{
              $pageTitle = "Manage STEP 2 - FHE";
              $headerAction = '<a href="/tes" class="btn btn-sm btn-primary" role="button">Back to list</a>';

              return view("slsu.tes.index-search", compact('registrations','tmpStudent'),[
                'pageTitle' => $pageTitle,
                'headerAction' => $headerAction
              ]);

            }
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

    public function markfhe(Request $request){
        try{
            $id = Crypt::decryptstring($request->id);

            if (empty($id))
              throw new Exception("Empty ID");

            $tmpStudent =  DB::connection(strtolower(session('campus')))
              ->table('students as s')
              ->leftjoin("course as c", "s.Course", "=", "c.id")
              ->where("s.StudentNo", $id)
              ->first();

            if (empty($tmpStudent))
              throw new Exception("Student not found.");

            $pref = GENERAL::getStudentDefaultEnrolment();

            if (empty($pref))
              throw new Exception("Invalid Student Preference.");

            $sy = $pref['SchoolYear'];
            $sem = $pref['Semester'];

            $ex = Registration::where("SchoolYear", $sy)
                    ->where("Semester", $sem)
                    ->where("StudentNo", $id)
                    ->first();

            if (empty($ex))
              throw new Exception("Error: Student not processed. Not yet encoded in the department");

            if ($ex['SchoolLevel'] != "Under Graduate"){
              throw new Exception("Error: FHE is for Under Graduate student only.");
            }

            if ($ex->finalize != 0 and $ex->TES != 0){
              throw new Exception("Error: Student is not marked as finalize on the department.");
            }

            if ($ex->TES != 0){
              throw new Exception("Error: Student processed. Already marked.");
            }

            $data = [
              'TES' => 1,
              'TESDate' => date('Y-m-d'),
              'TESBy' => auth()->user()->UserName,
              'TimeTES' =>  date('H:i:s'),
              'cur_num' => $tmpStudent->cur_num
            ];

            $save = Registration::where("StudentNo", $id)
              ->where("SchoolYear", $sy)
              ->where("Semester", $sem)
              ->update($data);
            if ($save){
              $log = new LogController();
              $sms = new SMSController();
              $smsdes = "Hi ".utf8_decode($tmpStudent->FirstName)."!\nCongrats! You are FREE from paying Tuition and other School Fees this sem. Proceed to your college for enrolment.";
              $Description = "You are FREE from paying Tuition and other School Fees this sem. Proceed to the registrar for validation.";
              $logdata = [
                'Description' => $Description,
                'StudentNo' => $id,
                'created_at' => date('Y-m-d H:i:s'),
                'AddedBy' => auth()->user()->UserName
              ];
              $log->savelogstudent($logdata);
              if (!empty($tmpStudent->ContactNo))
                $sms->send($tmpStudent->ContactNo, $smsdes, 'yes');

              return response()->json([
                'Error' => 0,
                'Message' => $id . " - ".$tmpStudent->LastName.', '.$tmpStudent->FirstName
              ]);
            }

            throw new Exception("Unable to set FHE");

        }catch(Exception $e){
            return response()->json([
              'Error' => 1,
              'Message' => $e->getMessage()
            ]);
        }catch(DecryptException $e){
          return response()->json([
            'Error' => 1,
            'Message' => "Invalid ID. Decryption Error"
          ]);
        }
    }

    public function marknonfhe(Request $request){
      try{
          $id = Crypt::decryptstring($request->id);
          $reason = $request->reason;

          if (empty($id))
            throw new Exception("Empty ID");

          if (empty($reason))
            throw new Exception("Empty Reason.");

          $tmpStudent =  DB::connection(strtolower(session('campus')))
            ->table('students as s')
            ->leftjoin("course as c", "s.Course", "=", "c.id")
            ->where("s.StudentNo", $id)
            ->first();

          if (empty($tmpStudent))
            throw new Exception("Student not found.");

          $pref = GENERAL::getStudentDefaultEnrolment();

          if (empty($pref))
            throw new Exception("Invalid Student Preference.");

          $sy = $pref['SchoolYear'];
          $sem = $pref['Semester'];

          $ex = Registration::where("SchoolYear", $sy)
                  ->where("Semester", $sem)
                  ->where("StudentNo", $id)
                  ->first();

          if (empty($ex))
            throw new Exception("Error: Student not processed. Not yet encoded in the department");

          if ($ex['SchoolLevel'] != "Under Graduate"){
            throw new Exception("Error: FHE is for Under Graduate student only.");
          }

          if ($ex->finalize != 0 and $ex->TES != 0){
            throw new Exception("Error: Student is not marked as finalize on the department.");
          }

          if ($ex->TES != 0){
            throw new Exception("Error: Student processed. Already marked.");
          }

          $data = [
            'TES' => 2,
            'TESReason' => $reason,
            'TESDate' => date('Y-m-d'),
            'TESBy' => auth()->user()->UserName,
            'TimeTES' =>  date('H:i:s'),
            'cur_num' => $tmpStudent->cur_num,
          ];

          $save = Registration::where("StudentNo", $id)
            ->where("SchoolYear", $sy)
            ->where("Semester", $sem)
            ->update($data);

          if ($save){
            $log = new LogController();
            $sms = new SMSController();
            $smsdes = "Hi ".utf8_decode($tmpStudent->FirstName)."!\nWe regret to inform you that you haven't availed the Free Higher Ed this semester. Comply the necessary documents first before enrolling. Reason: " .$reason;
            $Description = "We regret to inform you that you haven't availed the Free Higher Ed this semester. Comply the necessary documents first before enrolling. Reason: " .$reason;
            $logdata = [
              'Description' => $Description,
              'StudentNo' => $id,
              'created_at' => date('Y-m-d H:i:s'),
              'AddedBy' => auth()->user()->UserName
            ];
            $log->savelogstudent($logdata);
            if (!empty($tmpStudent->ContactNo))
              $sms->send($tmpStudent->ContactNo, $smsdes);

            return response()->json([
              'Error' => 0,
              'Message' => $id . " - ".$tmpStudent->LastName.', '.$tmpStudent->FirstName
            ]);
          }

          throw new Exception("Unable to set FHE");

      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getMessage()
          ]);
      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => "Invalid ID. Decryption Error"
        ]);
      }
  }

    public function resetfhe(Request $request){
      try{
          $id = Crypt::decryptstring($request->id);

          if (empty($id))
            throw new Exception("Empty ID");

          $tmpStudent =  DB::connection(strtolower(session('campus')))
            ->table('students as s')
            ->where("s.StudentNo", $id)
            ->first();

          if (empty($tmpStudent))
            throw new Exception("Student not found.");

          $pref = GENERAL::getStudentDefaultEnrolment();

          if (empty($pref))
            throw new Exception("Invalid Student Preference.");

          $sy = $pref['SchoolYear'];
          $sem = $pref['Semester'];

          $ex = Registration::where("SchoolYear", $sy)
                  ->where("Semester", $sem)
                  ->where("StudentNo", $id)
                  ->first();

          if (empty($ex))
            throw new Exception("Error: Student not yet processed.");

          // if ($ex->finalize != 1)
          //   throw new Exception("Error: Student's enrolment has been processed.");
          // $data = [
          //   'TES' => 0,
          //   'TESDate' => date('Y-m-d'),
          //   'TESBy' => auth()->user()->UserName,
          //   'TimeTES' =>  date('H:i:s')
          // ];

          //delete ni before
          $data = [
            'TES' => 0,
            'TESDate' => NULL,
            'TESBy' => NULL,
            'TESReason' => NULL,
            'TimeTES' => NULL
          ];
          $save = Registration::where("StudentNo", $id)
              ->where("SchoolYear", $sy)
              ->where("Semester", $sem)
              ->update($data);

          if ($save){
            $log = new LogController();
            $sms = new SMSController();
            $smsdes = "Hi ".utf8_decode($tmpStudent->FirstName)."!\nYour FHE status has been reverted. You may visit the office for clarifications.";
            $Description = "Your FHE status has been reverted. You may visit the office for clarifications.";
            $logdata = [
              'Description' => $Description,
              'StudentNo' => $id,
              'created_at' => date('Y-m-d H:i:s'),
              'AddedBy' => auth()->user()->UserName
            ];
            $log->savelogstudent($logdata);
            if (!empty($tmpStudent->ContactNo))
              $sms->send($tmpStudent->ContactNo, $smsdes);

            return response()->json([
              'Error' => 0,
              'Message' => $id . " - ".$tmpStudent->LastName.', '.$tmpStudent->FirstName
            ]);
          }

          throw new Exception("Unable to revert FHE");

      }catch(Exception $e){
          return response()->json([
            'Error' => 1,
            'Message' => $e->getLine() . ":".$e->getMessage()
          ]);
      }catch(DecryptException $e){
        return response()->json([
          'Error' => 1,
          'Message' => "Invalid ID. Decryption Error"
        ]);
      }
  }
}


