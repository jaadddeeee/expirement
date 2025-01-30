<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Http;

use App\Models\Employee;
use App\Models\User;

class ProfileController extends Controller
{

  protected $log;

  public function __construct(){
    $this->log = new LogController();
  }

  public function index()
  {
    $pageTitle = "My Profile";
    $headerAction = '';



    return view('slsu.my.profile',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
    ]);

  }

  public function account(){
    $pageTitle = "My Accounts";
    $headerAction = '';



    return view('slsu.my.account',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
    ]);

  }

  public function update(Request $request){

    $email = $request->email;

    try {

        if (empty($email))
            throw new Exception('Invalid Email.');

        $employees = Http::withToken(\GENERAL::API()['Token'])->post(\GENERAL::API()['URL'] . "/api/auth/from_sis_checkemail", [
          'email' => $email
        ])->json();

        if(empty($employees)){
            throw new Exception('Invalid Data.');
        }


        $data = [
          'FirstName' => $employees['result']['FirstName'],
          'MiddleName' => $employees['result']['MiddleName'],
          'LastName' => $employees['result']['LastName'],
          'Cellphone' => $employees['result']['Cellphone'],
          'EmploymentStatus' => $employees['result']['EmploymentStatus'],
          'AgencyNumber' => $employees['result']['AgencyNumber'],
          'CurrentItem' => (isset($employees['result']['currentitem']['ItemName'])?$employees['result']['currentitem']['ItemName']:0),
          'EmailAddress' => $employees['result']['EmailAddress'],
          'profilephoto' => $employees['result']['profilephoto'],
        ];

        $update = Employee::where("id", auth()->user()->Emp_No)
          ->update($data);


        $sso = User::where("id", auth()->user()->id)
          ->update([
            'HRMISID' => $employees['result']['id']
          ]);

        if ($sso)
          return response()->json(['Error' => 0, "Message" => "SSO Activated. You can now login using SSO"]);


    }catch (Exception $error) {
      // dd($error);
      $this->log->login([
        'Username' => session('username'),
        'SSO' => 1,
        'Remarks' => $error->getMessage()
      ]);

      return response()->json([
            'status_code' => 1,
            'Message' => $error->getMessage(),
      ]);
    }

  }

}
