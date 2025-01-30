<?php

namespace App\Http\Controllers\SLSU;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

use Crypt;
use GENERAL;
use App\Models\Enrolled;
use App\Models\Student;
use App\Models\LastSMS;

class SMSController
{

    protected $username;
    protected $password;
    protected $url;
    public function __construct(){
        $this->username = "noreply@southernleytestateu.edu.ph";
        $this->password = "N3stn13!";
        $this->url = "https://messagingsuite.smart.com.ph/cgpapi/messages/sms";
    }

    private function saveSent($data = []){
      if (!empty($data['StudentNo'])){
        $save = LastSMS::insert([
          'employee_id' => $data['EmployeeID'],
          'StudentNo' => $data['StudentNo'],
          'schedule_id' => $data['ScheduleID'],
          'month' => date('m'),
          'year' => date('Y'),
          'Message' => $data['Message']
        ]);
      }else{
        $save = LastSMS::insert([
          'employee_id' => $data['EmployeeID'],
          'schedule_id' => $data['ScheduleID'],
          'month' => date('m'),
          'year' => date('Y'),
          'Message' => $data['Message']
        ]);
      }


    }

    private function hasSent($data = []){
        if (!empty($data['StudentNo'])){
          $sent = LastSMS::where('schedule_id', $data['ScheduleID'])
            ->where('employee_id', $data['EmployeeID'])
            ->where('StudentNo', $data['StudentNo'])
            ->where('month', date('m'))
            ->where('year', date('Y'))
            ->first();
        }else{
          $sent = LastSMS::where('schedule_id', $data['ScheduleID'])
              ->where('employee_id', $data['EmployeeID'])
              ->where('month', date('m'))
              ->where('year', date('Y'))
              ->first();
        }

        $out = false;
        if (!empty($sent))
          $out = true;

        return $out;
    }

    public function send($contactnumber = "", $sms = "", $local = "no")
    {
        //new way
        $prefs = new Preference();
        $pref = $prefs->GetDefaults(['useSmart','canSendSMS']);
        $ret = "";
        if (strtolower($local) == "yes"){
          $campus = "sg";
          if ($prefs->GetDefaultValue($pref, "canSendSMS") == 1){
              $campus = session('campus');
          }
          $this->SendLocal($sms, $contactnumber, $campus);
        }else{
          if ($prefs->GetDefaultValue($pref, "useSmart") == 1){
            $ret = $this->SendSMS($contactnumber, $sms);
          }else{
              $campus = "sg";
              if ($prefs->GetDefaultValue($pref, "canSendSMS") == 1){
                  $campus = session('campus');
              }
              $this->SendLocal($sms, $contactnumber, $campus);
          }
        }
        return $ret;
    }

    public function SendSMS($contactnumber = "", $sms = ""){

        if (substr($contactnumber, 0,1) == "0"){
            $contactnumber = "63".substr($contactnumber, 1, 10);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json;charset=UTF-8',
            ])
        ->withOptions(['verify' => false])
        ->post($this->url,[
            "username" => $this->username,
            "password" => $this->password,
            "messageType" => "sms",
            "destination" => $contactnumber,
            "text" => $sms,
            "source" => GENERAL::Sender()
        ])->json();

        if (isset($response['status']) and $response['status'] == "ENROUTE"){
            return ["Error" => 0, "Message" => ""];
        }

        return ["Error" => 1, "Message" => $response['errorDescription']];
    }

    public function SendLocal($sms, $contactnumber, $campus){
        $sendSMS = ["ContactNo" => $contactnumber, "Message" => $sms, "UserName" => Auth::user()->UserName, "IPAddress" => request()->ip(), "Sent" => "No"];
        DB::connection(strtolower($campus))->table("smsstudent")
            ->insert($sendSMS);
    }

    public function bulksms(Request $request){
        $prefs = new Preference();
        $pref = $prefs->GetDefaults(['useSmart','canSendSMS']);

        try{

          if (empty($request->hidID))
            throw new Exception("Invalid ID");

          $id = Crypt::decryptstring($request->hidID);

          $msg = $request->BulkMessage;

          if (empty($msg))
            throw new Exception("Empty Message");

          if (strlen($msg) > 290){
            throw new Exception("Kindly keep your message within 290 characters. Your message has a size of ".strlen($msg));
          }

          $msg .= strip_tags(nl2br(htmlentities($msg)))."\n\nSender: ".auth()->user()->emp->FirstName.' '.auth()->user()->emp->LastName;
          $enrolled = new Enrolled();
          $lists = $enrolled->select('s.StudentNo','s.ContactNo')
            ->where("courseofferingid", $id)
            ->where("r.SchoolYear", session('schoolyear'))
            ->where("r.Semester", session("semester"))
            ->where("r.finalize", 1)
            ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
            ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
            ->orderBy("s.LastName")
            ->get();

          $ccOff = DB::connection(strtolower(session('campus')))
              ->table("courseoffering".session('schoolyear').session("semester"))
              ->where("id", $id)->first();

          // dd($ccOff);
          if (empty($ccOff)){
            throw new Exception("Cannot send SMS. Invalid Schedule.");
          }

          if (count($lists) <= 0){
              throw new Exception("Cannot send SMS. No student enrolled.");
          }

          if ($prefs->GetDefaultValue($pref, "useSmart") == 1){
            if ($this->hasSent(['ScheduleID' => $id, "EmployeeID" => $ccOff->teacher])){
                throw new Exception("Unable to send SMS. You have already sent a bulk message this month. Please try again next month. ");
            }else{
              foreach($lists as $list){
                if (!empty($list->ContactNo)){
                  $this->SendSMS($list->ContactNo, $msg);
                }
              }

              $this->saveSent([
                'EmployeeID' => $ccOff->teacher,
                'ScheduleID' => $id,
                'Message' => $msg
              ]);
            }
          }else{
              $campus = "sg";
              if ($prefs->GetDefaultValue($pref, "canSendSMS") == 1){
                $campus = session('campus');
              }

              foreach($lists as $list){
                if (!empty($list->ContactNo)){
                    $this->SendLocal($msg, $list->ContactNo, $campus);
                }
              }
          }
          return response()->json(['Error' => 0, 'Message' => GENERAL::Success("Messages sent")]);
        }catch(DecryptException $e){
            return response()->json(['Error' => 1, 'Message' => GENERAL::Error($e->getMessage())]);
        }catch(Exception $e){
            return response()->json(['Error' => 1, 'Message' => GENERAL::Error($e->getMessage())]);
        }


    }

    public function onesms(Request $request){
      $prefs = new Preference();
      $pref = $prefs->GetDefaults(['useSmart','canSendSMS']);

      try{

        if (empty($request->hidStudentID))
          throw new Exception("Invalid ID");

        $id = Crypt::decryptstring($request->hidStudentID);
        $msg = $request->BulkMessage;


        if (empty($msg))
          throw new Exception("Empty Message");

        $msg = strip_tags(nl2br(htmlentities($msg)))."\n\nSender: ".auth()->user()->emp->FirstName.' '.auth()->user()->emp->LastName;

        $list = Student::select('StudentNo','ContactNo')
          ->where("StudentNo", $id)
          ->first();

        if ($prefs->GetDefaultValue($pref, "useSmart") == 1){
            if (!empty($list->ContactNo)){
              $this->SendSMS($list->ContactNo, $msg);
              $this->saveSent([
                'EmployeeID' => auth()->user()->Emp_No,
                'ScheduleID' => '',
                'StudentNo' => $id,
                'Message' => $msg
              ]);
            }
        }else{
            $campus = "sg";
            if ($prefs->GetDefaultValue($pref, "canSendSMS") == 1){
              $campus = session('campus');
            }

            if (!empty($list->ContactNo)){
                $this->SendLocal($msg, $list->ContactNo, $campus);
            }
        }

        return response()->json(['Error' => 0, 'Message' => GENERAL::Success("Messages sent")]);
      }catch(DecryptException $e){
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error($e->getMessage())]);
      }catch(Exception $e){
          return response()->json(['Error' => 1, 'Message' => GENERAL::Error($e->getMessage())]);
      }


  }
}
