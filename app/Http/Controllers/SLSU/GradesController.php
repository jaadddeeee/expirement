<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\SLSU\SMSController;
use Illuminate\Contracts\Encryption\DecryptException;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Session;
use ROLE;
use GENERAL;
use Crypt;

class GradesController extends Controller
{

    protected $sms;
    public function __construct(){
        $this->sms = new SMSController();
    }

    public function save(Request $request){
        // dd($request->all());
        // $listgrades = Session::get('gradequery');
        $flag = $request->flag;
        // if (empty($listgrades))
          // return response()->json(['Error' => 1, 'Message' => "Invalid Grade Query"]);

        if (sizeof($flag) <= 0)
          return response()->json(['Error' => 1, 'Message' => "Invalid List"]);

        $out = [];

        foreach($flag as $pref){

            // $mtr = "mt-".$f;
            $pref = Crypt::decryptstring($pref);
            $mt = $request->{"mt-".$pref};
            $ft = $request->{"ft-".$pref};
            $f = $request->{"f-".$pref};

            if (!empty($mt) or !empty($ft) or !empty($f)){

                $error = 0;
                if (!empty($mt)){
                  if (!GENERAL::validGrades($mt, 'mt'))
                    $error = 1;
                }

                if (!empty($ft)){
                  if (!GENERAL::validGrades($ft, 'ft'))
                    $error = 1;
                }

                if (!empty($f)){
                  if (!GENERAL::validGrades($f, 'f'))
                    $error = 1;
                }

                if ($error == 1){
                    array_push($out, ['Error' => 1, "ID" => $pref]);
                }else{

                    try{
                      $tmp = explode("_", $pref);
                      $g = "grades".$tmp[1].$tmp[2];
                      $sRec = DB::connection(strtolower(session('campus')))
                          ->table($g." as g")
                          ->select("t.courseno", "s.FirstName", "s.ContactNo")
                          ->leftjoin("students as s", "g.StudentNo", "=", "s.StudentNo")
                          ->leftjoin("transcript as t", "g.sched", "=", "t.id")
                          ->where("g.id", $tmp[0])
                          ->where("g.StudentNo", $tmp[3])
                          ->first();
                      // dd($gRes);
                      if (empty($sRec)){
                          array_push($out, ['Error' => 1, "ID" => $pref]);
                      }else{

                        $mtGrade = (empty($mt)?0:$mt);
                        $ftGrade = (empty($ft)?0:$ft);
                        $fGrade = (empty($f)?0:$f);

                        $data = ['midterm' => $mtGrade, 'finalterm' => $ftGrade, 'final' => $fGrade];
                        $gRes = DB::connection(strtolower(session('campus')))
                          ->table($g)
                          ->where("id", $tmp[0])
                          ->where("StudentNo", $tmp[3])
                          ->update($data);

                        if ($gRes){
                          if (!empty($sRec->ContactNo)){
                              $msg = date('dM G:i')."\nHello ".strtoupper($sRec->FirstName)."!\n\nYour grades for " . $sRec->courseno . " (".$tmp[1]."-".GENERAL::Semesters()[$tmp[2]]['Short'].") has been updated.\n\nMT=".(empty($mtGrade)?"0":GENERAL::GradeRemarks($mtGrade))."\nFT=".(empty($ftGrade)?"0":GENERAL::GradeRemarks($ftGrade))."\nF=".(empty($fGrade)?"0":GENERAL::GradeRemarks($fGrade));
                              $this->sms->send($sRec->ContactNo, $msg, "yes");
                          }

                          array_push($out, ['Error' => 0, "ID" => $pref,'disabled' => (empty($f)?false:true)]);

                        }else{
                          array_push($out, ['Error' => 1, "ID" => $pref]);
                        }

                      }
                    }catch(Exception $e){
                        return response()->json(['Error' => 1, 'Message' => $e->getMessage()]);
                    }

                }

            }


        }
        return response()->json(['Error' => 0, "Message" => $out]);

    }

    public function upload(Request $request){

      $file =  (isset($request->fileexcel)?$request->fileexcel:"");

      try{
          $id = (isset($request->hidID)?Crypt::decryptstring($request->hidID):"") ;
          $g = (isset($request->hidTable)?Crypt::decryptstring($request->hidTable):"") ;
          $hidTotalRows = (isset($request->hidTotalRows)?Crypt::decryptstring($request->hidTotalRows):"") ;
      }catch(DecryptException $e){
          return response()->json(['Error' => 1, 'Message' => "Invalid ID"]);
      }

      if (empty($file))
          return response()->json(['Error' => 1, 'Message' => "Please select valid excel file"]);

      $the_file = $request->file('fileexcel');

      try{

          $spreadsheet = IOFactory::load($the_file->getRealPath());

          if (!in_array("Gradesheet", $spreadsheet->getSheetNames()))
            throw new Exception("Invalid Excel file");

          $sheet  = $spreadsheet->setActiveSheetIndexByName("Gradesheet");

          $column_limit = $sheet->getHighestDataColumn();

          if (strtolower($column_limit) != "l")
            throw new Exception("Invalid Excel file");



          // $row_limit    = $sheet->getHighestDataRow();


          $row_range    = range(7, $hidTotalRows + 6);
          // $column_range = range('A', $column_limit );
          // $startcount = ;
          // $data = array();

          $listError = [];
          $totalRecord = 0;
          $error = 0;
          $totalImport = 0;
          $toImport = [];
          foreach ( $row_range as $row ) {

              $totalRecord++;

              $studentno = $sheet->getCell( 'C' . $row )->getCalculatedValue();
              $mt = (empty($sheet->getCell( 'H' . $row )->getCalculatedValue())?0:(is_numeric($sheet->getCell( 'H' . $row )->getCalculatedValue())?$sheet->getCell( 'H' . $row )->getCalculatedValue():0));
              $ft = (empty($sheet->getCell( 'I' . $row )->getCalculatedValue())?0:(is_numeric($sheet->getCell( 'I' . $row )->getCalculatedValue())?$sheet->getCell( 'I' . $row )->getCalculatedValue():0));
              $f = (empty($sheet->getCell( 'J' . $row )->getCalculatedValue())?0:(is_numeric($sheet->getCell( 'J' . $row )->getCalculatedValue())?$sheet->getCell( 'J' . $row )->getCalculatedValue():0));

              //check if naa cja grado
              $exGrade = DB::connection(strtolower(session('campus')))->table($g)
                ->where("StudentNo", $studentno)
                ->where("courseofferingid", $id)
                ->first();

              if (empty($exGrade)){
                array_push($listError, ["<b>Row ". $row.":</b> StudentNo <b>".$studentno."</b> not found in the selected schedule, Skipping."]);
                $error++;
              }else{
                if (!empty($exGrade->final)){
                    array_push($listError, ["<b>Row ". $row.":</b> StudentNo <b>".$studentno."</b> has already a grade, Skipping."]);
                    $error++;
                }else{
                    array_push($toImport, [
                        'StudentNo' => $studentno,
                        'MT' => number_format($mt,1,".",""),
                        'FT' => number_format($ft,1,".",""),
                        'Final' => number_format($f,1,".","")
                    ]);

                    $totalImport++;
                }
              }


          }

          return response()->json([
              'TotalRecord' => $totalRecord,
              'TotalError' => $error,
              'TotalImport' => $totalImport,
              'ErrorList' => $listError,
              'ImportGrades' => $toImport
          ]);


          // return redirect()->route('fines.batch')->withSuccess("Fines successfully imported");
          // dd($data);
      } catch (Exception $e) {

          return response()->json(['Error' => 1, 'Message' => "Please select valid excel file : ".$e->getMessage()]);
          // return back()->withErrors('There was a problem uploading the data!');
      } catch (\Maatwebsite\Excel\Exceptions\SheetNotFoundException $e) {
          return response()->json(['Error' => 1, 'Message' => "Invalid Excel file"]);
      }
  }
}
