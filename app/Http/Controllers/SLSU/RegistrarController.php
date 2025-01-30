<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\ClearanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Role;
use App\Models\Student;
use App\Models\Course;
use App\Models\Purpose;
use App\Models\CLRegistrar;
use App\Models\Department;
use App\Models\Registration;

use GENERAL;

class RegistrarController extends Controller
{
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

    public function add(Request $request){
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

        $tmpStudent = Student::where("StudentNo", $dataTMP[0])->first();

        if (empty($tmpStudent))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Student not found. ")]);

        $ex = CLRegistrar::where("StudentNo", $dataTMP[0])
            ->where("Description", $reason)
            ->where("SchoolYear", $sy)
            ->where("Semester", $sem)
            ->first();

        if (!empty($ex))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry on the selected student")]);


        $ins = CLRegistrar::create([
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
            "Description" => "One entry has been added to your Registrar clearance",
            "StudentNo" => $dataTMP[0],
            "AddedBy" => Auth::user()->Emp_No,
            "created_at" => date('Y-m-d h:i:s')
          ];

          $log->savelogstudent($data);
          return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Added to the list. Changes will be reflected after the modal is closed")]);
        }


        return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Unable to save student")]);
    }

    public function remove(Request $request){
      $id = (isset($request->id)?\Crypt::decryptstring($request->id):0);

      if (empty($id))
        return response()->json(['Error' => 1, "Message" => "Invalid ID"]);

      $one = CLRegistrar::find($id);

      if (empty($one))
        return response()->json(['Error' => 1, "Message" => "Data not found."]);

      $del = $one->delete();

      if ($del){

        $clr = new ClearanceController();
        $clr->reCheckClearance($one->StudentNo);

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

    public function upload(Request $request){

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
                }else{
                    $ex = CLRegistrar::where("StudentNo", $studentno)
                      ->where("Description", $reason)
                      ->where("SchoolYear", $sy)
                      ->where("Semester", $sem)
                      ->first();

                    if (!empty($ex)){
                        array_push($listError, [$studentno. ' is already exist.' ]);
                        $error++;
                    }else{
                        $ins = CLRegistrar::create([
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

    public function studentsearch(Request $request){

      $id = $request->id;

      $depts = CLRegistrar::select('clearance_registrar.*')
      ->leftjoin('students as s', "clearance_registrar.StudentNo", "=", 's.StudentNo')
      ->where(function($q) use ($id){
          $q->Orwhere("clearance_registrar.StudentNo","LIKE","%{$id}%")
          ->Orwhere("s.LastName","LIKE","%{$id}%")
          ->Orwhere("s.FirstName","LIKE","%{$id}%");
      })
      ->get();

      return view('slsu.clearance.customosas', compact('depts'));
    }

    //end clearance function

    // Start Certificates

    public function certificates(){
      $pageTitle = "Generate Certificate";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $purposes = Purpose::orderby("Description", "ASC")->get();
      return view('slsu.certificates.index', compact('purposes'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);

    }

    // End Certificates

    //Start Reports
    public function unencoded(){
      $pageTitle = "Unencoded Grades";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $departments = Department::orderby("Description", "ASC")
          ->where("Active", 0)->get();

      return view('slsu.registrar.unencoded', compact('departments'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);

    }

    public function prounencoded(Request $request){

      try {
          $departmentid = \Crypt::decryptstring($request->department);
          $SchoolYear = $request->SchoolYear;
          $Semester = $request->Semester;

          if (empty($SchoolYear))
            throw new Exception('Empty School Year');
          if (empty($Semester))
            throw new Exception('Empty Semester');
      }catch(Exception $e){
          return "<div class = 'alert alert-danger'>".$e->getMessage()."</div>";
      } catch (DecryptException $e) {
          return "<div class = 'alert alert-danger'>Invalid ID</div>";
      }

      $g = "grades".$SchoolYear.$Semester;
      $cc = "courseoffering".$SchoolYear.$Semester;
      DB::connection(strtolower(session('campus')))->statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
      $regs = Registration::select("e.LastName", "e.FirstName", "cc.coursecode", "d.DepartmentName",
          DB::connection(strtolower(session('campus')))->raw('COUNT(g.id) as Unecoded'))
          ->leftjoin($g." as g", "registration.RegistrationID", "=", "g.gradesid")
          ->leftjoin($cc." as cc", "g.courseofferingid", "=", "cc.id")
          ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
          ->leftjoin("department as d", "e.Department", "=", "d.id")
          ->where("registration.finalize", 1)
          ->where("registration.SchoolYear", $SchoolYear)
          ->where("registration.Semester", $Semester)
          ->where("g.final", 0)
          ->groupby("cc.teacher")
          ->groupby("cc.id")
          ->orderby("e.LastName")
          ->orderby("e.FirstName");

          if (!empty($departmentid)){
            $regs = $regs->where("e.Department", $departmentid);
          }
          $regs = $regs->get();
      // dd($regs);
      DB::connection(strtolower(session('campus')))->statement("SET sql_mode=(SELECT CONCAT(@@sql_mode, ',ONLY_FULL_GROUP_BY'));");
      return view("slsu.registrar.list-unencoded", compact('regs'));
    }

    public function formslkra(){
        $pageTitle = "Form SL KRA1.1 WFTEs";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.registrar.report.formslkra', [
          'pageTitle' => $pageTitle,
          'headerAction' => $headerAction
        ]);
    }

    public function proformslkra(Request $request){
        $from = $request->SchoolYearFrom;
        $to = $request->SchoolYearTo;

        $sems = [1,2];
        if ($from > $to)
          return GENERAL::Error("Invalid Date");

        $out = [];

        $lvl = ['Under Graduate' ,'Masteral','Doctoral'];

        for($start=$from;$start<=$to;$start++){
            foreach($sems as $sem){
              try{
                $g = "grades".$start.$sem;
                $reg = Registration::select("registration.SchoolYear","registration.Semester","registration.Course",
                    DB::connection(strtolower(session('campus')))->raw("SUM(t.lab) as SumLab"),
                    DB::connection(strtolower(session('campus')))->raw("SUM(t.lec) as SumLec")
                    )
                    ->leftjoin($g . " as g", "registration.RegistrationID", "=", "g.gradesid")
                    ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                    ->where("registration.SchoolYear", $start)
                    ->where("registration.Semester", $sem)
                    ->where("registration.finalize",1)
                    ->where("t.exempt","<>",1)
                    ->groupby("registration.Course")
                    ->groupby("registration.SchoolYear")
                    ->groupby("registration.Semester")
                    ->get()->toArray();
                array_push($out,$reg);
              }catch(\Exception $e){
                  return GENERAL::Error("Invalid Date or School Year does not exist");
              }
            }
        }

        $heads_array = [];
        for($start=$from;$start<=$to;$start++){
            foreach($sems as $sem){
                $heads = Registration::select("registration.SchoolYear","registration.Semester","registration.Course",
                          DB::connection(strtolower(session('campus')))->raw("COUNT(registration.StudentNo) as CountHead")
                      )
                      ->where("registration.SchoolYear", $start)
                      ->where("registration.Semester", $sem)
                      ->where("registration.finalize",1)
                      ->groupby("registration.Course")
                      ->groupby("registration.SchoolYear")
                      ->groupby("registration.Semester")
                      ->get()->toArray();

                array_push($heads_array, $heads);
            }
        }


        $courses = Course::where("isActive",0)
            ->orderBy("lvl")
            ->orderBy("course_title")
            ->get();

       return view('slsu.registrar.report.formslkrapro', compact('out','courses','heads_array'),[
          "From" => $from,
          "To" => $to,
          "Sems" => $sems,
          'lvl' => $lvl
       ]);
    }

    public function arta(){
      $pageTitle = "ARTA";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $forevaluations = DB::connection("evaluation")
          ->table("ratesettings")
          ->whereNull("deleted_at")
          ->orderBy("SchoolYear")
          ->orderBy("Semester")
          ->get();

      return view('slsu.registrar.report.arta', compact('forevaluations'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);
    }

    public function proarta(Request $request){

      try{
        $id = Crypt::decryptstring($request->Period);

        if (empty($id))
          throw new Exception("Please select period");

        $evals = DB::connection("evaluation")
          ->table("ratesettings")
          ->where("id", $id)
          ->first();

        if (empty($evals))
          throw new Exception("Period not found.");

        $tblresults = "result".$evals->SchoolYear.$evals->Semester;

        if (!Schema::connection("evaluation")->hasTable($tblresults))
          throw new Exception("No data found");

        $results = DB::connection("evaluation")
          ->table($tblresults)
          ->where("Campus", strtoupper(session('campus')))
          ->get();

        $groupResults = $results->groupby("StudentNo");
        $students = [];
        foreach($groupResults as $gResult){
            array_push($students, $gResult[0]->StudentNo);
        }

        $listStudents = Student::select("StudentNo", "Sex","BirthDate")
            ->whereIn("StudentNo", $students)
            ->get();

        $g1 = DB::connection("evaluation")
              ->table("feedbackform")
              ->where("GroupSequence", 1)
              ->get();

        $g2 = DB::connection("evaluation")
              ->table("feedbackform")
              ->where("GroupSequence", 2)
              ->orderBy("Sequence")
              ->get();
        $subs = DB::connection("evaluation")
            ->table("feedbackformsub")
            ->get();

        return view('slsu.registrar.report.artapro', compact('subs','listStudents','groupResults','results','g1','g2','evals'));

      }catch(DecryptException $err){
        return GENERAL::Error($err->getMessage());
      }catch(\Exception $err){
        return GENERAL::Error($err->getMessage());
      }

  }

    //End Reports
}
