<?php

namespace App\Http\Controllers\SLSU\Report;

use App\Http\Controllers\SLSU\Report\GradesheetController;
use App\Http\Controllers\SLSU\LogController;
use App\Http\Controllers\SLSU\TrackerController;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

use App\Exports\ORFAssessment;
use App\Exports\RouteSlip;
use App\Exports\DataPrivacy;
use App\Exports\EnrolmentForm;
use App\Exports\AFESPDF;
use App\Models\Enrolled;
use App\Models\Student;
use App\Models\Registration;
use GENERAL;
use ROLE;
class ReportController
{

    public function afespdf(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $pdf = new AFESPDF('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(78);
      $pdf::SetLeftMargin(15);
      $pdf::SetRightMargin(15);
      $pdf::SetAutoPageBreak(TRUE,55);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = $pdf->getName()."-afes-".$date.".pdf";

      $public = "public";
      $directoryPath = 'afes';
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function enrolmentform(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $pdf = new EnrolmentForm('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(40);
      $pdf::SetLeftMargin(15);
      $pdf::SetRightMargin(15);
      $pdf::SetAutoPageBreak(TRUE,10);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = $pdf->getName()."-enrolment-form-".$date.".pdf";

      $public = "public";
      $directoryPath = 'enrolmentform/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function dataprivacy(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $pdf = new DataPrivacy('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(40);
      $pdf::SetLeftMargin(15);
      $pdf::SetRightMargin(15);
      $pdf::SetAutoPageBreak(TRUE,10);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = $pdf->getName()."-data-privacy-".$date.".pdf";

      $public = "public";
      $directoryPath = 'dataprivacy/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function routeslip(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $pdf = new RouteSlip('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(40);
      $pdf::SetAutoPageBreak(TRUE,10);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = $pdf->getName()."-route-slip-".$date.".pdf";

      $public = "public";
      $directoryPath = 'routeslip/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function orfassessment(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);

      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $pdf = new ORFAssessment('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(40);
      $pdf::SetAutoPageBreak(TRUE,10);
      $pdf->List();
      $pdf->Assessment();
      $date = date("Y-m-d-h-i-s");
      $fname = $pdf->getName()."-orf-assessment-".$date.".pdf";

      $public = "public";
      $directoryPath = 'orfassessment/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function gradesheet(Request $request){

      try{
          $id = Crypt::decryptstring($request->id);
          $sy = Crypt::decryptstring($request->sy);
          $sem = Crypt::decryptstring($request->sem);
      }catch(DecryptException $e){
        session(['ErrorBlob' => "Invalid Hash"]);
        return false;
      }

      $g = "grades".$sy.$sem;

      $en = Enrolled::select(DB::connection(strtolower(session('campus')))->raw('count(final) as cID'))
          ->leftjoin("registration as r", $g.".gradesid", "=", "r.RegistrationID")
          ->where("r.SchoolYear", $sy)
          ->where("r.Semester", $sem)
          ->where("r.finalize", 1)
          ->where("final", "<=", 0)
          ->where("courseofferingid", $id)
          ->first();

      if ($en->cID > 0){
        session(['ErrorBlob' => "Unable to generate gradesheet. Please input grades for the remaining ". $en->cID  . " students."]);
        return false;
      }

      $pdf = new GradesheetController('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);
      $pdf->setSy($sy);
      $pdf->setSem($sem);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });



      $pdf::AddPage();
      $pdf::SetTopMargin(120);
      $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = "GradeSheet-".$date.".pdf";

      $pdf::Output(storage_path("app/public/petition/".$fname),'F');

      $log = new LogController();
      $track = new TrackerController();
      $log->gradesheet([
        'EmployeeID' => auth()->user()->Emp_No,
        'CourseOfferingID' => $id,
        'SchoolYear' => $sy,
        'Semester' => $sem,
        'DateGenerated' => date('Y-m-d'),
        'Platform' => Browser::platformName(),
        'Browser' => Browser::browserFamily(),
        'Device' => Browser::deviceFamily() . ' ' . Browser::deviceModel()
      ]);

      $track->gradesheet([
        'EmployeeID' => auth()->user()->Emp_No,
        'CourseOfferingID' => $id,
        'SchoolYear' => $sy,
        'Semester' => $sem,
        'DateGenerated' => date('Y-m-d')
      ]);

      $public = "public";
      $directoryPath = 'gradesheet/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);

    }

    public function workload(Request $request){

      try{
          $id = Crypt::decryptstring($request->id);
          $sy = Crypt::decryptstring($request->sy);
          $sem = Crypt::decryptstring($request->sem);

      }catch(DecryptException $e){
        return GENERAL::Error("Invalid Hash");
      }

      if (strtolower(session('schoolyear')) != $sy or strtolower(session('semester')) != $sem)
        return response()->json(['Error' => "Invalid School Year/Semester. You may have opened another tab and selected another preferences"]);


      $pdf = new WorkloadController('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);
      $pdf->setSy($sy);
      $pdf->setSem($sem);

      // HEADER


      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      $pdf::SetTopMargin(93);
      $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = "Workload-".$date.".pdf";
      $public = "public";
      $directoryPath = 'workload/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function certificate(Request $request){

        if (empty($request->Type))
          return response()->json(['Error' => "Invalid Type"]);

        if ($request->Type == 1)
          return $this->coe($request);

        if ($request->Type == 2)
          return $this->employmentboard($request);

        if ($request->Type == 3)
          return $this->goodmoral($request);

        if ($request->Type == 4)
          return $this->goodmoralojt($request);

        if ($request->Type == 5)
          return $this->endorsement($request);

        if ($request->Type == 6)
          return $this->certificationgrad($request);

        if ($request->Type == 7)
          return $this->ctc($request);

    }

    public function certificationgrad(Request $request){
      $allsearch = $request->allsearch;
      $tmp = explode(" - ", $allsearch);
      $LatinHonor = $request->LatinHonor;
      $ORNo = $request->ORNo;
      $ORDate = $request->ORDate;
      $Purpose = $request->Purpose;
      $DocORNo = $request->DocORNo;
      $DocORDate = $request->DocORDate;
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();

      $error = [];

      if (empty($student)){
        $error[] = "Invalid Student";
      }
      if (empty($student->grad)){
        $error[] = "Student is not flag as graduated. To set, search the student and put value on the graduation date.";
      }
      if (empty($Purpose)){
        $error[] = "No purpose selected.";
      }
      if (empty($ORNo) or empty($ORDate) or empty($DocORNo) or empty($DocORDate)){
        $error[] = "ORNos and Dates must be provided";
      }

      if (!empty($error)){
        $pdf = new ErrorPDF('P', 'cm', array(215.9,330.2));
        $pdf->setError(implode(", ",$error));
      }else{
        $pdf = new CertificateGrad('P', 'cm', array(215.9,330.2));
        $pdf->setStudentno($studentNo);
        $pdf->setLatinHonor($LatinHonor);
        $pdf->setReason($Purpose);
        $pdf->setOr($ORNo);
        $pdf->setOrdate($ORDate);
        $pdf->setDocor($DocORNo);
        $pdf->setDocordate($DocORDate);
      }


      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "Endorsement-".$pdf->getName().'-'.$date.".pdf";
      $public = "public";
      $directoryPath = 'endorsement/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function goodmoralojt(Request $request){
      $allsearch = $request->allsearch;
      $sy = $request->SchoolYear;
      $sem = $request->Semester;
      $ORNo = $request->ORNo;
      $ORDate = $request->ORDate;
      $Purpose = $request->Purpose;

      $tmp = explode(" - ", $allsearch);
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();

      if (empty($student))
        return response()->json(['Error' => "Invalid Student"]);



      $pdf = new CGoodMoralOJT('P', 'cm', array(215.9,330.2));

      $pdf->setSy($sy);
      $pdf->setSem($sem);
      $pdf->setReason($Purpose);
      $pdf->setOr($ORNo);
      $pdf->setOrdate($ORDate);
      $pdf->setStudentno($studentNo);
      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "COEmployment-".$date.".pdf";
      $public = "public";
      $directoryPath = 'goodmoralojt/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function endorsement(Request $request){
      $allsearch = $request->allsearch;
      $tmp = explode(" - ", $allsearch);
      $LatinHonor = $request->LatinHonor;
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();

      if (empty($student)){
        $pdf = new ErrorPDF('P', 'cm', array(215.9,330.2));
        $pdf->setError("Invalid Student");
      }elseif (empty($student->grad)){
        $pdf = new ErrorPDF('P', 'cm', array(215.9,330.2));
        $pdf->setError("Student is not flag as graduated. To set, search the student and put value on the graduation date.");
      }else{
        $pdf = new Endorsement('P', 'cm', array(215.9,330.2));
        $pdf->setStudentno($studentNo);
        $pdf->setLatinHonor($LatinHonor);
      }


      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "Endorsement-".$pdf->getName().'-'.$date.".pdf";
      $public = "public";
      $directoryPath = 'endorsement/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function goodmoral(Request $request){
      $allsearch = $request->allsearch;
      $sy = $request->SchoolYear;
      $sem = $request->Semester;
      $ORNo = $request->ORNo;
      $ORDate = $request->ORDate;
      $Purpose = $request->Purpose;

      $tmp = explode(" - ", $allsearch);
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();

      if (empty($student))
        return response()->json(['Error' => "Invalid Student"]);



      $pdf = new CGoodMoral('P', 'cm', array(215.9,330.2));

      $pdf->setSy($sy);
      $pdf->setSem($sem);
      $pdf->setReason($Purpose);
      $pdf->setOr($ORNo);
      $pdf->setOrdate($ORDate);
      $pdf->setStudentno($studentNo);
      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "COEmployment-".$date.".pdf";
      $public = "public";
      $directoryPath = 'goodmoral/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function employmentboard(Request $request){
      $allsearch = $request->allsearch;
      $sy = $request->SchoolYear;
      $sem = $request->Semester;
      $ORNo = $request->ORNo;
      $ORDate = $request->ORDate;
      $Purpose = $request->Purpose;
      $DocORNo = $request->DocORNo;
      $DocORDate = $request->DocORDate;

      $tmp = explode(" - ", $allsearch);
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();

      if (empty($student))
        return response()->json(['Error' => "Invalid Student"]);



      $pdf = new CEmploymentBoard('P', 'cm', array(215.9,330.2));

      $pdf->setSy($sy);
      $pdf->setSem($sem);
      $pdf->setReason($Purpose);
      $pdf->setOr($ORNo);
      $pdf->setOrdate($ORDate);
      $pdf->setStudentno($studentNo);
      $pdf->setDocor($DocORNo);
      $pdf->setDocordate($DocORDate);
      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "COEmployment-".$date.".pdf";
      $public = "public";
      $directoryPath = 'coeboard/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function coe(Request $request){
      $allsearch = $request->allsearch;
      $sy = $request->SchoolYear;
      $sem = $request->Semester;
      $ORNo = $request->ORNo;
      $ORDate = $request->ORDate;
      $Purpose = $request->Purpose;
      $DocORNo = $request->DocORNo;
      $DocORDate = $request->DocORDate;

      $tmp = explode(" - ", $allsearch);
      $studentNo = $tmp[0];

      $student = Student::where("StudentNo", $studentNo)->first();
      $reg = Registration::where("StudentNo", $studentNo)
          ->where("SchoolYear", $sy)
          ->where("Semester", $sem)
          ->where("finalize", 1)
          ->first();


      if (empty($student)){
        $error[] = "Invalid Student";
      }
      if (empty($reg)){
        $error[] = "Student is not enrolled on the selected options.";
      }
      if (empty($Purpose)){
        $error[] = "No purpose selected.";
      }
      if (empty($sy)){
        $error[] = "No SY selected";
      }
      if (empty($sem)){
        $error[] = "No Sem selected";
      }
      if (empty($ORNo) or empty($ORDate) or empty($DocORNo) or empty($DocORDate)){
        $error[] = "ORNos and Dates must be provided";
      }

      if (!empty($error)){
        $pdf = new ErrorPDF('P', 'cm', array(215.9,330.2));
        $pdf->setError(implode(", ",$error));
      }else{
        $pdf = new COE('P', 'cm', array(215.9,330.2));

        $pdf->setSy($sy);
        $pdf->setSem($sem);
        $pdf->setReason($Purpose);
        $pdf->setOr($ORNo);
        $pdf->setOrdate($ORDate);
        $pdf->setStudentno($studentNo);
        $pdf->setDocor($DocORNo);
        $pdf->setDocordate($DocORDate);
      }





      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();

      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      // $pdf::SetTopMargin(93);
      // $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf::SetMargins(20, 93, 20);
      // $pdf::SetAutoPageBreak(TRUE,75);
      $pdf->Content();
      $date = date("Y-m-d-h-i-s");
      $fname = "COE-".$date.".pdf";

      $public = "public";
      $directoryPath = 'coe/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');
      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function qfin65(Request $request){
      try{
        $id = Crypt::decryptstring($request->id);
        $sy = Crypt::decryptstring($request->sy);
        $sem = Crypt::decryptstring($request->sem);
        $cashier = Crypt::decryptstring($request->cashier);

      }catch(DecryptException $e){
        return GENERAL::Error("Invalid Hash");
      }

      session([
        'schoolyear' => $sy,
        'semester' => $sem
      ]);

      $pdf = new QFIN65Controller('P', 'cm', array(215.9,330.2));
      $pdf->setId($id);
      $pdf->setSy($sy);
      $pdf->setSem($sem);


      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();
      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      $pdf::SetTopMargin(93);
      $pdf::SetAutoPageBreak(TRUE,40);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = "QF-IN65-".$date.".pdf";

      $public = "public";
      $directoryPath = 'petition/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');

      if ($cashier == 1){
          $cc = "courseoffering".$sy.$sem;

          $enrolled = new Enrolled();

          $lists = $enrolled->select($enrolled->getTable().".*", "r.Course", "r.SchoolLevel")
          ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
          ->leftjoin($cc ." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
          ->where("r.SchoolYear", $sy)
          ->where("r.Semester", $sem)
          ->where("cc.coursecode", $id)
          ->get();

          if (count($lists) > 0){
            foreach($lists as $list){
                $del = DB::connection(strtolower(session('campus')))
                    ->table("adjust")
                    ->where("StudentNo", $list->StudentNo)
                    ->where("CourseNo", $list->sched)
                    ->where("SchoolYear", $sy)
                    ->where("Semester", $sem)
                    ->delete();

                $del = DB::connection(strtolower(session('campus')))
                    ->table("adjust")
                    ->insert([
                      'CourseNo' => $list->sched,
                      'StudentNo' => $list->StudentNo,
                      'SchoolYear' => $sy,
                      'Semester' => $sem,
                      'Amount' => $pdf->getFee(),
                      'Course' => $list->Course,
                      'StudentLevel' => $list->SchoolLevel
                    ]);
            }
          }

      }

      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function ctc(Request $request){
      try{
        $allsearch = $request->allsearch;
        $tmp = explode(" - ", $allsearch);
        $studentNo = $tmp[0];
        $NameofSchool = $request->NameofSchool;
        $TransferAddress = $request->TransferAddress;

      }catch(DecryptException $e){
        return GENERAL::Error("Invalid Hash");
      }

      $pdf = new CTCController('P', 'cm', array(215.9,330.2));
      $pdf->setId($studentNo);
      $pdf->setSchoolname($request->NameofSchool);
      $pdf->setAddress($request->TransferAddress);
      $pdf->setOrno($request->ORNo);
      $pdf->setDocordate($request->DocORDate);
      $pdf->setDocor($request->DocORNo);
      // HEADER

      $pdf::setHeaderCallback(function($p) use ($pdf){
        $pdf->Header();
      });

      $pdf::setFooterCallback(function($p) use ($pdf){
        $pdf->Footer();
      });

      $pdf::AddPage();
      $pdf::SetTopMargin(10);
      $pdf::SetAutoPageBreak(TRUE,10);
      $pdf->List();
      $date = date("Y-m-d-h-i-s");
      $fname = "CTC-".$date.".pdf";

      $public = "public";
      $directoryPath = 'ctc/'.session('campus');
      if (!Storage::exists($public."/".$directoryPath)) {
        Storage::makeDirectory($public."/".$directoryPath);
      }

      $pdf::Output(storage_path("app/public/".$directoryPath."/".$fname),'F');

      return response()->download('storage/'.$directoryPath.'/'.$fname);
    }

    public function count(Request $request){
      $pageTitle = "ENROLMENT COUNT";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $sy = 2024;
      $sem = 2;
      $campus = session('campus');
      if (auth()->user()->AllowSuper == 1 or ROLE::isVPAA() or ROLE::isPresident()){
        if (isset($request->Campus)){
          $campus = $request->Campus;
        }
      }

      if (isset($request->SchoolYear)){
        $sy = $request->SchoolYear;
      }

      if (isset($request->Semester)){
        $sem = $request->Semester;
      }

      if (auth()->user()->AllowSuper == 1 or ROLE::isRegistrar() or strtolower(auth()->user()->AccountLevel) == "administrator" or ROLE::isVPAA() or ROLE::isPresident()){
        $programs = DB::connection(strtolower($campus))
          ->table('course as c')
          ->select('c.*')
          ->where('isActive', 0)
          ->whereNot('lvl', 'Highschool')
          ->orderby('accro')
          ->get();
      }else{
        $programs = DB::connection(strtolower($campus))
        ->table("accountcourse as ac")
        ->leftjoin('course as c', 'ac.CourseID', '=', 'c.id')
        ->select('c.*')
        ->where("UserName", strtolower(auth()->user()->Emp_No))
        ->whereNot('c.lvl', 'Highschool')
        ->get();
      }

      $counts = DB::connection(strtolower($campus))
        ->table('registration as r')
        ->select('r.Course','r.StudentYear','s.Sex', DB::connection(strtolower($campus))->raw('count(r.id) as countEnrolled'))
        ->leftjoin('students as s', 'r.StudentNo', '=', 's.StudentNo')
        ->where('r.finalize', 1)
        ->where('r.SchoolYear', $sy)
        ->where('r.Semester', $sem)
        ->whereIn('r.Course', $programs->pluck('id')->toArray())
        ->whereNot('r.SchoolLevel', "Highschool")
        ->groupby('r.Course')
        ->groupby('r.StudentYear')
        ->groupby('s.Sex')
        ->get();

      return view('slsu.report.count', compact('programs','counts'), [
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'Campus' => $campus,
        'SchoolYear' => $sy,
        'Semester' => $sem
      ]);
    }
}

