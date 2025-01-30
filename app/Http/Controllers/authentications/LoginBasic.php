<?php

namespace App\Http\Controllers\authentications;

use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\SLSU\SMSController;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use Exception;

use App\Models\User;
use App\Models\Employee;
use Browser;

class LoginBasic extends Controller
{
  protected $log;
  protected $sms;
  protected $api;

  public function __construct(){
    $this->log = new LogController();
    $this->sms = new SMSController();
    $this->api = "http://192.168.0.11/hrmis";
  }

  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  public function indexadmin()
  {

    return view('content.authentications.auth-login-basic-admin');
  }

  public function sso(Request $request){

      $email = $request->email;
      try {

          $employees = Http::withToken(\GENERAL::API()['Token'])->post(\GENERAL::API()['URL'] . "/api/auth/from_sis_checkemail", [
            'email' => $email
          ])->json();

          if(empty($employees)){
              throw new Exception('Invalid Data.');
          }

          $campus = \GENERAL::CampusbyNo($employees['result']['Campus']);

          session([
            'campus' => $campus
          ]);

          if (empty(session('campus'))){
            throw new Exception('Invalid Campus');
          }

          $res = Employee::where('EmailAddress', $email)->first();
          if (empty($res)){
              RateLimiter::hit($this->throttleKey(), $seconds = 3600);
              throw new Exception('Your Email Address is not connected to SSO');
          }

          $users = User::where("Emp_No", $res->id)
              ->where("HRMISID", $employees['result']['id'])
              ->first();

          if (empty($users)){
              RateLimiter::hit($this->throttleKey(), $seconds = 3600);
              throw new Exception('Your Email Address is not connected to SSO. Login using your existing account and enable your account to connect to SSO');
          }

          session([
            'username' => $users->UserName
          ]);

          if ($users->Active == 2){
            throw new Exception("Account is inactive");
          }

          if ($users->emp->EmploymentStatus == "Part Timer" or $users->emp->EmploymentStatus == "Job Order"){
            if ($users->DateEndActive == "2001-01-01"){
              throw new Exception('The account has already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
            }elseif ($users->DateActive == "2001-01-01"){
              throw new Exception('The account has already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
            }elseif ($users->DateActive > date('Y-m-d')){
              throw new Exception('The account is inactive');
            }elseif ($users->DateEndActive < date('Y-m-d')){
              throw new Exception('Account is already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
            }

          }

          if (!empty($users->HRMISID)){
            $hrmis = DB::connection('hrmis')
              ->table('employee as e')
              ->leftjoin('cscitemname as csc', 'e.CurrentItem', '=', 'csc.id')
              ->select('e.EmploymentStatus','e.Cellphone', 'csc.ItemName','e.EmailAddress')
              ->where('e.id', $users->HRMISID)
              ->first();

            if (!empty($hrmis)){
              Employee::where('id', $users->Emp_No)
                ->update([
                  'EmploymentStatus' => $hrmis->EmploymentStatus,
                  'Cellphone' => $hrmis->Cellphone,
                  'CurrentItem' => $hrmis->ItemName,
                  'EmailAddress' => $hrmis->EmailAddress
                ]);
            }
          }

          Auth::login($users);

          RateLimiter::clear($this->throttleKey());

          $this->log->login([
            'Username' => auth()->user()->UserName,
            'SSO' => 1,
            'Remarks' => "Successful login"
          ]);

          if (!empty(auth()->user()->emp->Cellphone))
            $this->sms->send(auth()->user()->emp->Cellphone,'Hi '.auth()->user()->emp->FirstName.'! You have logged-in to CES on ' . date('Y-M-d H:i:s') . ' using SSO via '. Browser::browserFamily().'. If you did not recognize this, kindly reach us.');

          return response()->json([
                'status_code' => 0,
                'Message' => "",
          ]);

      } catch (Exception $error) {
          // dd($error);
          $this->log->login([
            'Username' => session('username'),
            'SSO' => 1,
            'Remarks' => $error->getMessage()
          ]);

          return response()->json([
                'status_code' => 1,
                'Message' => \GENERAL::Error($error->getMessage()),
          ]);
      }

  }

  public function login(Request $request){

      try {
          $campus = Crypt::decryptstring($request->campus);;
      } catch (DecryptException $e) {
          $campus = "";
      }

      $data = request(['username', 'password']);

      session([
          'campus' => $campus,
          'haslogged' => false,
          'username' => $request->username
      ]);


      $pword = sha1(trim($data['password'])."nestnie");

      try {

          if (empty($request->campus))
            throw new Exception('Empty campus');

          if (empty($request->username))
            throw new Exception('Empty username');

          if (empty($request->password))
            throw new Exception('Empty password');

          $this->checkTooManyFailedAttempts();

          $student = User::where("UserName", $data['username'])
              ->where("Password", $pword)
              ->first();

          if (empty($student))
          {
              RateLimiter::hit($this->throttleKey(), $seconds = 3600);

              throw new Exception('Invalid username or password. Attempts remaining: '. RateLimiter::remaining($this->throttleKey(), 11));
          }

          if ($student->Active == 2){
            throw new Exception("Account is inactive");
          }

          if ($student->emp->EmploymentStatus == "Part Timer" or $student->emp->EmploymentStatus == "Job Order"){
              if ($student->DateEndActive == "2001-01-01"){
                throw new Exception('The account has already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
              }elseif ($student->DateActive == "2001-01-01"){
                throw new Exception('The account has already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
              }elseif ($student->DateActive > date('Y-m-d')){
                throw new Exception('The account is inactive');
              }elseif ($student->DateEndActive < date('Y-m-d')){
                throw new Exception('Account is already expired. If it is an error, kindly visit MIS/ISU Office to update your account.');
              }
          }

          if (!empty($student->HRMISID)){
            $hrmis = DB::connection('hrmis')
              ->table('employee as e')
              ->leftjoin('cscitemname as csc', 'e.CurrentItem', '=', 'csc.id')
              ->select('e.EmploymentStatus','e.Cellphone', 'csc.ItemName','e.EmailAddress')
              ->where('e.id', $student->HRMISID)
              ->first();

            if (!empty($hrmis)){
              Employee::where('id', $student->Emp_No)
                ->update([
                  'EmploymentStatus' => $hrmis->EmploymentStatus,
                  'Cellphone' => $hrmis->Cellphone,
                  'CurrentItem' => $hrmis->ItemName,
                  'EmailAddress' => $hrmis->EmailAddress
                ]);
            }
          }

          Auth::login($student);

          RateLimiter::clear($this->throttleKey());

          if (!empty(auth()->user()->emp->Cellphone))
            $this->sms->send(auth()->user()->emp->Cellphone,'Hi '.auth()->user()->emp->FirstName.'! You have logged-in to CES on ' . date('Y-M-d H:i:s') . ' using '. Browser::browserFamily().'. If you did not recognize this, kindly reach us.');

          $this->log->login([
            'Username' => auth()->user()->UserName,
            'SSO' => 0,
            'Remarks' => "Successful login"
          ]);

          return response()->json([
                'status_code' => 0,
                'Message' => "",
          ]);

      } catch (Exception $error) {
          // dd($error);

          $this-> log->login([
            'Username' => session('username'),
            'SSO' => 0,
            'Remarks' => $error->getMessage()
          ]);

          return response()->json([
                'status_code' => 1,
                'Message' => $error->getMessage(),
          ]);
      }

  }

  // public function admin(Request $request){

  //   try {
  //       $campus = Crypt::decryptstring($request->campus);;
  //   } catch (DecryptException $e) {
  //       $campus = "";
  //   }

  //   $data = request(['username', 'password']);

  //   session([
  //       'campus' => $campus,
  //       'haslogged' => false,
  //       'username' => $request->username
  //   ]);


  //   $pword = sha1(trim($data['password'])."nestnie");

  //   try {

  //       if (empty($request->campus))
  //         throw new Exception('Empty campus');

  //       if (empty($request->username))
  //         throw new Exception('Empty username');

  //       if (empty($request->password))
  //         throw new Exception('Empty password');

  //       $this->checkTooManyFailedAttempts();

  //       $loginadmin = User::where("UserName", 'admin')
  //           ->where("Password", $pword)
  //           ->first();

  //       if (empty($loginadmin))
  //       {
  //           RateLimiter::hit($this->throttleKey(), $seconds = 3600);
  //           throw new Exception('Invalid admin username or password. Attempts remaining: '. RateLimiter::remaining($this->throttleKey(), 11));
  //       }

  //       $student = User::where("UserName", $data['username'])
  //           ->first();

  //       if (empty($student))
  //       {
  //           RateLimiter::hit($this->throttleKey(), $seconds = 3600);
  //           throw new Exception('Invalid username or password. Attempts remaining: '. RateLimiter::remaining($this->throttleKey(), 11));
  //       }

  //       Auth::login($student);
  //       RateLimiter::clear($this->throttleKey());

  //       // dd($loginadmin->emp->Cellphone);
  //       if (!empty($loginadmin->emp->Cellphone))
  //         $this->sms->send($loginadmin->emp->Cellphone,'Hi '.$loginadmin->emp->FirstName.'! You have logged-in as bypass to CES on ' . date('Y-M-d H:i:s') . ' using '. Browser::browserFamily().'. If you did not recognize this, kindly reach us.');

  //       $this->log->login([
  //         'Username' => $loginadmin->UserName,
  //         'SSO' => 0,
  //         'Remarks' => "Successful login bypass"
  //       ]);

  //       return response()->json([
  //             'status_code' => 0,
  //             'Message' => "",
  //       ]);

  //   } catch (Exception $error) {
  //       // dd($error);

  //       $this-> log->login([
  //         'Username' => session('username'),
  //         'SSO' => 0,
  //         'Remarks' => $error->getMessage()
  //       ]);

  //       return response()->json([
  //             'status_code' => 1,
  //             'Message' => $error->getMessage(),
  //       ]);
  //   }

  // }

  public function throttleKey()
  {
      return Str::lower(request('username')) . '|' . request()->ip();
  }


  public function checkTooManyFailedAttempts()
  {
      if (! RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
          return;
      }

      $seconds = RateLimiter::availableIn($this->throttleKey());

      throw new Exception('Too many login attempts. Try again in '. gmdate("H:i:s", $seconds));
  }

  public function destroy()
    {

        Auth::guard('web')->logout();
        Auth::logout();
        return redirect('/');
    }
}
