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
use Illuminate\Support\Facades\Storage;

use App\Exports\PRCGraduation;
use Exception;
use App\Models\Student;
use GENERAL;

class CHEDReportController extends Controller
{
  public function graduationlist(Request $request){

    $date = date('Y');
    if (isset($request->YearOfGraduation))
      $date = $request->YearOfGraduation;

    $listgraduations = Student::select("grad", DB::connection(strtolower(session('campus')))->raw('COUNT(grad) as cGrad'))
      ->where('grad', "LIKE", "%".$date)
      ->groupby("grad")
      ->get();


    $lists = [];
    if (count($listgraduations) > 0){

      $dateGrad = $listgraduations[0]->grad;
      if (isset($request->DateOfGraduation))
        $dateGrad = $request->DateOfGraduation;

      $lists = Student::select('students.*','c.course_title')
        ->leftjoin("course as c", "students.Course", "=", "c.id")
        ->where('grad', $dateGrad)
        ->orderby("LastName")
        ->orderby("FirstName")
        ->get();
    }

    if (!isset($request->DateOfGraduation)){
      $pageTitle = "PRC Graduation List";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';


      return view('slsu.ched.graduationlist', compact('listgraduations','lists'),[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'Year' => $date,
        'DateGrad' => $dateGrad
        ]);
    }else{
      return view('slsu.ched.lists', compact('lists'));
    }

  }

  public function graduationdatelist(Request $request){
    $listgraduations = Student::select("grad", DB::connection(strtolower(session('campus')))->raw('COUNT(grad) as cGrad'))
    ->where('grad', "LIKE", "%".$request->id)
    ->groupby("grad")
    ->get();

    return response()->json($listgraduations);
  }

  public function pdf(Request $request){

    $pdf = new PRCGraduation('L', 'cm', array(330.2, 215.9));
    $pdf->setId($request->DateOfGraduation);
    $pdf->setSy($request->SchoolYear);
    $pdf->setSem($request->Semester);

    // HEADER


    $pdf::setHeaderCallback(function($p) use ($pdf){
      $pdf->Header();

    });

    $pdf::setFooterCallback(function($p) use ($pdf){
      $pdf->Footer();
    });

    $pdf::AddPage('L', array(215.9, 330.2));
    $pdf::SetTopMargin(57);
    $pdf::SetAutoPageBreak(TRUE,20);
    $pdf->Body();
    $date = \Str::slug($request->DateOfGraduation);

    $fname = "prcgraduation-".$date.".pdf";

    $public = "public";
    $directoryPath = 'prcgraduation/'.session('campus');
    if (!Storage::exists($public."/".$directoryPath)) {
      Storage::makeDirectory($public."/".$directoryPath);
    }

    $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');

    return response()->download('storage/'.$directoryPath.'/'.$fname);
  }
}


