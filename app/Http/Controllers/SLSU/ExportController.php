<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ClassRecord;
use App\Exports\QFIN67;
use Illuminate\Support\Facades\Storage;
use Excel;

class ExportController extends Controller
{
  public function classrecord(Request $request)
  {

      return Excel::download(new ClassRecord($request),$request->code.'-'.$request->courseno.'-classrecord.xlsx');
  }

  public function qfin67(Request $request)
  {
      $pdf = new QFIN67('P', 'cm', array(215.9,330.2));
      $pdf->setData($request->all());
      // HEADER
      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });
      $pdf::SetMargins(20, 20, 20, true);
      $pdf::AddPage();
      $pdf::SetTopMargin(120);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Body();
      $date = date("Y-m-d-h-i-s");
      $fname = "QF-IN67-".$pdf->getId().'-'.$pdf->getSy().'-'.$pdf->getSem().".pdf";

      $public = "public";
      $directoryPath = 'out-out-qfin67/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);

  }
}
