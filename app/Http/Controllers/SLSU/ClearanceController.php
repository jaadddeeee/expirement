<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Crypt;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Role;
use App\Models\CLDepartment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use App\Models\CLLibrary;
use App\Models\CLRegistrar;
use App\Models\CLBARGO;
use App\Models\CLOSAS;
use App\Models\CLDORMB;
use App\Models\CLDORMG;
use App\Models\CLGUIDANCE;
use App\Models\CLCLINIC;
use App\Models\CLMIS;
use App\Models\UserStudent;

use GENERAL;
class ClearanceController extends Controller
{
    protected $deptID = 0;
    public function index()
    {

      $role = Role::where("EmpID", Auth::user()->Emp_No)
              ->where("Role", "Clearance")
              ->first();

      if (empty($role))
        \GENERAL::Error("Invalid Role");

      if (strtolower($role->ClearanceRole) == "department"){

        if (empty($role->DepartmentID))
            \GENERAL::Error("Invalid Role");


        $dept = Department::where("id", $role->DepartmentID)->first();

        session([
          'DepartmentID' => $role->DepartmentID
        ]);

        $ids = Role::select("EmpID")
            ->where("DepartmentID", $role->DepartmentID)->pluck("EmpID");

        $pageTitle = "Manage Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        $depts = CLDepartment::whereIn("AddedBy", $ids)->paginate(50);
        // dd($depts);
        return view('slsu.clearance.department', compact('depts'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'Role' => "Department",
          'DepartmentName' => $dept->DepartmentName
        ]);
      }elseif (strtolower($role->ClearanceRole) == "library"){
          $pageTitle = "Manage Clearance";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
          $depts = CLLibrary::paginate(50);
          // dd($depts);
          return view('slsu.clearance.library', compact('depts'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Role' => "Library"
          ]);
      }elseif (strtolower($role->ClearanceRole) == "osas"){
          $pageTitle = "Manage Clearance";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
          $depts = CLOSAS::paginate(50);
          // dd($depts);
          return view('slsu.clearance.osas', compact('depts'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Role' => "OSAS"
          ]);
      }elseif (strtolower($role->ClearanceRole) == "registrar"){
          $pageTitle = "Manage Clearance";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
          $depts = CLRegistrar::paginate(50);
          // dd($depts);
          return view('slsu.clearance.registrar', compact('depts'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Role' => "Registrar"
          ]);
      }elseif (strtolower($role->ClearanceRole) == "mis"){
          $pageTitle = "Manage Clearance";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
          $depts = CLMIS::paginate(50);
          // dd($depts);
          return view('slsu.clearance.mis', compact('depts'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Role' => "MIS"
          ]);
      }elseif (strtolower($role->ClearanceRole) == "bargo"){
          $pageTitle = "Manage Clearance";
          $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
          $depts = CLBARGO::paginate(50);
          // dd($depts);
          return view('slsu.clearance.bargo', compact('depts'), [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'Role' => "BARGO"
          ]);
      }elseif (strtolower($role->ClearanceRole) == "dormb"){
        $pageTitle = "Manage Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        $depts = CLDORMB::paginate(50);
        // dd($depts);
        return view('slsu.clearance.dormb', compact('depts'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'Role' => "DORMITORY"
        ]);
      }elseif (strtolower($role->ClearanceRole) == "dormg"){
        $pageTitle = "Manage Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        $depts = CLDORMG::paginate(50);
        // dd($depts);
        return view('slsu.clearance.dormg', compact('depts'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'Role' => "DORMITORY"
        ]);
      }elseif (strtolower($role->ClearanceRole) == "guidance"){
        $pageTitle = "Manage Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        $depts = CLGUIDANCE::paginate(50);
        // dd($depts);
        return view('slsu.clearance.guidance', compact('depts'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'Role' => "GUIDANCE"
        ]);
      }elseif (strtolower($role->ClearanceRole) == "clinic"){
        $pageTitle = "Manage Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        $depts = CLCLINIC::paginate(50);
        // dd($depts);
        return view('slsu.clearance.clinic', compact('depts'), [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
          'Role' => "CLINIC"
        ]);
      }else{
        return \GENERAL::Error("You are unauthorized to view this page");
      }
    }

    public function search(Request $request): JsonResponse
    {


      $str = $request->str;

      $role = Role::where("EmpID", Auth::user()->Emp_No)
              ->where("Role", "Clearance")
              ->first();

      if (strtolower($role->ClearanceRole) == "department"){

        $courses = Course::select("id")
            ->where("Department", session('DepartmentID'))
            ->pluck("id");

        $data = Student::select(DB::connection(strtolower(session('campus')))->raw('concat(StudentNo, " - ",LastName, ", ",FirstName) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("LastName","LIKE","%{$str}%")
                      ->whereOr("FirstName","LIKE","%{$str}%")
                      ->whereOr("StudentNo","LIKE","%{$str}%");
                })
                ->whereIn("Course", $courses)
                ->pluck('Name');
      }else{
          $data = Student::select(DB::connection(strtolower(session('campus')))->raw('concat(StudentNo, " - ",LastName, ", ",FirstName) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("LastName","LIKE","%{$str}%")
                      ->whereOr("FirstName","LIKE","%{$str}%")
                      ->whereOr("StudentNo","LIKE","%{$str}%");
                })
                ->pluck('Name');
      }

        return response()->json($data);
    }

    public function adddepartment(Request $request){
        $studentno = $request->str;
        $reason = $request->Description;
        $sy = $request->SchoolYear;
        $sem = $request->Semester;

        if (empty($studentno))
            return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please enter student no / name")]);
        if (empty($reason))
            return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please enter reason")]);
        if (empty($sy))
            return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select school year")]);
        if (empty($sem))
            return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select semester")]);

        $dataTMP = explode(" - ", $studentno);

        if (sizeof($dataTMP) != 2)
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid Student format. ")]);

        if (!$this->canbeadded($dataTMP[0]))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid Student. Not from your college/department ")]);

        $tmpStudent = Student::where("StudentNo", $dataTMP[0])->first();

        if (empty($tmpStudent))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Student not found. ")]);

        $ex = CLDepartment::where("StudentNo", $dataTMP[0])
            ->where("Description", $reason)
            ->where("SchoolYear", $sy)
            ->where("Semester", $sem)
            ->first();

        if (!empty($ex))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry on the selected student")]);


        $ins = CLDepartment::create([
          'StudentNo' => $dataTMP[0],
          'SchoolYear' => $sy,
          'Semester' => $sem,
          'Description' => $reason,
          'AddedBy' => Auth::user()->Emp_No
        ]);

        if ($ins){

          $upforClear = DB::connection(strtolower(session('campus')))
            ->table("registration")
            ->where("StudentNo", $dataTMP[0])
            ->update(["isCleared" => 2]);

          $log = new LogController();

          $data = [
            "Description" => "One entry has been added to your department clearance",
            "StudentNo" => $dataTMP[0],
            "AddedBy" => Auth::user()->Emp_No,
            "created_at" => date('Y-m-d h:i:s')
          ];

          $log->savelogstudent($data);
          return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Added to the list. Changes will be reflected after the modal is closed")]);
        }


        return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Unable to save student")]);
    }

    public function removeDepartment(Request $request){
      $id = (isset($request->id)?\Crypt::decryptstring($request->id):0);

      if (empty($id))
        return response()->json(['Error' => 1, "Message" => "Invalid ID"]);

      $one = CLDepartment::find($id);

      if (empty($one))
        return response()->json(['Error' => 1, "Message" => "Data not found."]);

      $del = $one->delete();

      if ($del){

        $this->reCheckClearance($one->StudentNo);

        $log = new LogController();

        $data = [
          "Description" => "One entry has been removed from department clearance entry",
          "StudentNo" => $one->StudentNo,
          "AddedBy" => Auth::user()->Emp_No,
          "created_at" => date('Y-m-d h:i:s')
        ];

        $log->savelogstudent($data);
        return response()->json(['Error' => 0]);
      }

      return response()->json(['Error' => 1, "Message" => "Unable to remove data."]);
    }

    public function uploaddepartment(Request $request){

        $file =  (isset($request->fileexcel)?$request->fileexcel:"");

        if (empty($file))
            return response()->json(['Error' => 1, 'Message' => "Please select valid excel file"]);

        $the_file = $request->file('fileexcel');

        try{
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range    = range(1, $row_limit );
            $column_range = range( 'A', $column_limit );
            // $startcount = ;
            // $data = array();

            $array_sem = [1,2,9];
            $listError = [];
            $totalRecord = 0;
            $error = 0;
            $totalImport = 0;
            foreach ( $row_range as $row ) {
                $totalRecord++;
                $studentno = $sheet->getCell( 'A' . $row )->getValue();
                $sy = (empty($sheet->getCell( 'B' . $row )->getValue())?0:(is_numeric($sheet->getCell( 'B' . $row )->getValue())?$sheet->getCell( 'B' . $row )->getValue():0));
                $sem = (empty($sheet->getCell( 'C' . $row )->getValue())?0:(is_numeric($sheet->getCell( 'C' . $row )->getValue())?$sheet->getCell( 'C' . $row )->getValue():0));
                $reason = $sheet->getCell( 'D' . $row )->getValue();
                //check if exist

                if (empty($studentno) or empty($sy) or empty($sem) or empty($reason)){
                    array_push($listError, ["Entry/ies from row" . $totalRecord . " is/are empty. Entry not imported"]);
                    $error++;
                }elseif(!in_array($sem, $array_sem)){
                    array_push($listError, ["Invalid semester for row " . $totalRecord . ". Choices are 1,2 and 9 for summer."]);
                    $error++;
                }elseif (!$this->canbeadded($studentno)){
                    array_push($listError, ["Invalid student for row " . $totalRecord . ". ".$studentno. " is not from your college/department"]);
                    $error++;
                }else{
                    $ex = CLDepartment::where("StudentNo", $studentno)
                      ->where("Description", $reason)
                      ->where("SchoolYear", $sy)
                      ->where("Semester", $sem)
                      ->first();

                    if (!empty($ex)){
                        array_push($listError, [$studentno. ' is already exist.' ]);
                        $error++;
                    }else{
                        $ins = CLDepartment::create([
                          'StudentNo' => $studentno,
                          'SchoolYear' => $sy,
                          'Semester' => $sem,
                          'Description' => $reason,
                          'AddedBy' => Auth::user()->Emp_No
                        ]);

                        if ($ins){
                            $totalImport++;

                            $upforClear = DB::connection(strtolower(session('campus')))
                              ->table("registration")
                              ->where("StudentNo", $studentno)
                              ->update(["isCleared" => 2]);

                            $log = new LogController();

                            $data = [
                              "Description" => "One entry has been added to your department clearance",
                              "StudentNo" => $studentno,
                              "AddedBy" => Auth::user()->Emp_No,
                              "created_at" => date('Y-m-d h:i:s')
                            ];

                            $log->savelogstudent($data);
                        }else{
                          $error++;
                        }
                    }
                }
            }

            return response()->json([
                'TotalRecord' => $totalRecord,
                'TotalError' => $error,
                'TotalImport' => $totalImport,
                'ErrorList' => $listError
            ]);


            // return redirect()->route('fines.batch')->withSuccess("Fines successfully imported");
            // dd($data);
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }

    public function canbeadded($snum = "")
    {

        $courses = Course::select("id")
            ->where("Department", session('DepartmentID'))
            ->pluck("id");

        $out = false;
        $data = Student::select("StudentNo")
                ->where("StudentNo",$snum)
                ->whereIn("Course", $courses)
                ->first();
        if (!empty($data))
          $out = true;
        return $out;
    }

    public function studentsearch(Request $request){

      $id = $request->id;

      $ids = Role::select("EmpID")
        ->where("DepartmentID", session('DepartmentID'))->pluck("EmpID");

      $depts = CLDepartment::select('clearance_department.*')
      ->leftjoin('students as s', "clearance_department.StudentNo", "=", 's.StudentNo')
      ->whereIn("AddedBy", $ids)
      ->where(function($q) use ($id){
          $q->Orwhere("clearance_department.StudentNo","LIKE","%{$id}%")
          ->Orwhere("s.LastName","LIKE","%{$id}%")
          ->Orwhere("s.FirstName","LIKE","%{$id}%");
      })
      ->get();

      return view('slsu.clearance.customdepartment', compact('depts'));

    }


    public function reCheckClearance($studentno){

      $clear = 1;

      //FINES

      $fines = DB::connection(strtolower(session('campus')))->table('fines')
          ->where("StudentNo", $studentno)
          ->where("paid", 0)
          ->where("Amount", ">", 0)
          ->whereNull("ORNo")
          ->get();

      if (sizeof($fines) > 0)
        $clear = 2;

      $osas = DB::connection(strtolower(session('campus')))->table('clearance_osas')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);

      $bargo = DB::connection(strtolower(session('campus')))->table('clearance_bargo')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);

      $lib = DB::connection(strtolower(session('campus')))->table('clearance_library')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);

      $reg = DB::connection(strtolower(session('campus')))->table('clearance_registrar')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);

      $dept = DB::connection(strtolower(session('campus')))->table('clearance_department')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);

      $obl = DB::connection(strtolower(session('campus')))->table('clearance_obligations')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno);


      $mis = DB::connection(strtolower(session('campus')))->table('clearance_mis')
        ->select('StudentNo', 'Description', 'deleted_at')
        ->whereNull("deleted_at")
        ->where('StudentNo', $studentno)
        ->union($osas)
        ->union($bargo)
        ->union($lib)
        ->union($reg)
        ->union($dept)
        ->union($obl)
        ->get();

      if (sizeof($mis) > 0)
        $clear = 2;

      $cash = DB::connection(strtolower(session('campus')))->table("registration")
        ->select("Balance")
        ->where("StudentNo", $studentno)
        ->where("finalize", 1)
        ->where("Balance", ">", 0)
        ->get();

      if (sizeof($cash) > 0)
        $clear = 2;

      $up = DB::connection(strtolower(session('campus')))->table("registration")
        ->where("StudentNo",$studentno)
        ->update(["isCleared" => $clear]);

    }

    public function student(){
        // $students = UserStudent::with('student')
        //   ->wherehas('student', function($query){
        //     $query->where("LastName", "LIKE", "H%");
        //   })
        //   ->where("isActive",0)
        //   ->where('forClearance', 1)
        //   ->get();

        $students = UserStudent::select('accountstudents.*','s.LastName','s.FirstName','c.accro','d.DepartmentName')
          ->leftjoin("students as s", "accountstudents.StudentNo", "=", "s.StudentNo")
          ->leftjoin("course as c", "s.Course", "=", "c.id")
          ->leftjoin("department as d", "c.Department", "=", "d.id")
          ->where("accountstudents.isActive",0)
          ->where('accountstudents.forClearance', 1)
          ->orderby("ClearanceFlag")
          ->orderby("s.LastName")
          ->orderby("s.FirstName")
          ->get();

        $pageTitle = "Manage Student Clearance";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.clearance.users-student',compact('students'),[
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction,
        ]);
    }

    public function studentsave(Request $request){
        try{
          $studentnotmp = $request->studentinfo;
          $aType = $request->Accountype;
          $valid = ['SSC','Department'];
          if (empty($aType))
            throw new Exception ("Invalid type:1.");

          if (!in_array($aType, $valid)){
            throw new Exception ("Invalid type:2.");
          }

          $tmp = explode(" - ", $studentnotmp);
          if (sizeof($tmp) != 2){
            $studentno = $studentnotmp;
          }else{
            $studentno = trim($tmp[0]);
          }
          $student = Student::where("StudentNo", $studentno)->first();
          if (empty($student))
            throw new Exception ("Student not found.");

          $hasAccount = UserStudent::where("StudentNo", $studentno)
            ->where("forclearance", 1)
            ->first();
          if (!empty($hasAccount))
            throw new Exception ("Student has already an account.");

          $course = Course::where("id", $student->Course)->first();
          if (empty($course))
            throw new Exception ("Student has an invalid course.");

          if (empty($course->Department))
            throw new Exception ("Student has no department.");

          $data = [
            'forclearance' => 1,
            'ClearanceFlag' => $aType,
            'Department' => $course->Department
          ];

          $save = UserStudent::where("StudentNo", $studentno)
            ->update($data);

          if (!$save)
            throw new Exception('Unable to save account');

          return GENERAL::Success($studentnotmp."'s account saved.");
        }catch(Exception $e)
        {
          return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
        }
    }

    public function studentdelete(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

        $hasAccount = UserStudent::where("id", $id)
          ->first();
        if (empty($hasAccount))
          throw new Exception ("Record not found.");

        $data = [
          'forclearance' => 0,
          'ClearanceFlag' => null,
          'Department' => null
        ];

        $save = UserStudent::where("id", $id)
          ->update($data);

        if (!$save)
          throw new Exception('Unable to remove account');

        return GENERAL::Success($hasAccount->StudentNo."'s account has been deleted.");
      }catch(Exception $e)
      {
        return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
      }
      catch(DecryptException $e)
      {
        return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
      }
  }
}
