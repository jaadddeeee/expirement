<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\ClearanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Role;
use App\Models\Student;
use App\Models\CLCLINIC;
use App\Models\Purpose;

class ClinicController extends Controller
{
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

        $ex = CLCLINIC::where("StudentNo", $dataTMP[0])
            ->where("Description", $reason)
            ->where("SchoolYear", $sy)
            ->where("Semester", $sem)
            ->first();

        if (!empty($ex))
          return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry on the selected student")]);


        $ins = CLCLINIC::create([
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
            "Description" => "One entry has been added to your dormitory clearance",
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

      $one = CLCLINIC::find($id);

      if (empty($one))
        return response()->json(['Error' => 1, "Message" => "Data not found."]);

      $del = $one->delete();

      if ($del){

        $clr = new ClearanceController();
        $clr->reCheckClearance($one->StudentNo);

        $log = new LogController();

        $data = [
          "Description" => "One entry has been removed from dormitory clearance entry",
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
                    $ex = CLCLINIC::where("StudentNo", $studentno)
                      ->where("Description", $reason)
                      ->where("SchoolYear", $sy)
                      ->where("Semester", $sem)
                      ->first();

                    if (!empty($ex)){
                        array_push($listError, [$studentno. ' is already exist.' ]);
                        $error++;
                    }else{
                        $ins = CLCLINIC::create([
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
                              "Description" => "One entry has been added to your dormitory clearance",
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

      $depts = CLCLINIC::select('clearance_osas.*')
      ->leftjoin('students as s', "clearance_osas.StudentNo", "=", 's.StudentNo')
      ->where(function($q) use ($id){
          $q->Orwhere("clearance_osas.StudentNo","LIKE","%{$id}%")
          ->Orwhere("s.LastName","LIKE","%{$id}%")
          ->Orwhere("s.FirstName","LIKE","%{$id}%");
      })
      ->get();

      return view('slsu.clearance.customosas', compact('depts'));
    }


    public function certificates(){
      $pageTitle = "Generate Certificate";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $purposes = Purpose::orderby("Description", "ASC")->get();
      return view('slsu.certificates.indexosas', compact('purposes'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction
      ]);

    }

}
