<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\LoginLog;
use Browser;

class LogController extends Controller
{

    public function savelogstudent($data){
        $sve = DB::connection(strtolower(session('campus')))
          ->table("logsforstudent")
          ->insert($data);
    }

    public function login($data = []){

        $p = "Desktop";
        if (\GENERAL::isMobile())
          $p = "Mobile";

        LoginLog::create([
            'UserName' => $data['Username'],
            'IPAddress' => \GENERAL::getIp(),
            'Emp_No' => (isset(auth()->user()->Emp_No)?auth()->user()->Emp_No:""),
            'Platform' => Browser::platformName(),
            'UserID' => (isset(auth()->user()->id)?auth()->user()->id:""),
            'SSO' => (empty($data['SSO'])?0:$data['SSO']),
            'Remarks' => $data['Remarks'],
            'Browser' => Browser::browserFamily(),
            'Device' => Browser::deviceFamily() . ' ' . Browser::deviceModel()
        ]);
    }

    public function fee($data = []){
      $sve = DB::connection(strtolower(session('campus')))
      ->table("logstudentfee")
      ->insert($data);
    }

    public function gradesheet($data = []){

      $sve = DB::connection(strtolower(session('campus')))
      ->table("loggradesheet")
      ->insert($data);
    }

    public function saveaction($data){
      $sve = DB::connection(strtolower(session('campus')))
        ->table("logsubjects")
        ->insert($data);
    }
}
