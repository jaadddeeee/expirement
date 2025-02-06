<?php

namespace App\Http\Controllers\SLSU;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Student2;
use App\Models\Registration;
use Illuminate\Support\Facades\Crypt;
use TCPDF;
use Carbon\Carbon;
use App\Services\StudentId;

class StudentIdController extends Controller
{

    public function index(Request $request)
    {
        $student = Student::all();

        $pageTitle = "School ID Card";
        // $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.studentid.index', [
            'pageTitle' => $pageTitle,
            // 'headerAction' => $headerAction
            'student' => $student
        ]);
    }
    
    public function getprocessid(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);
        
        $student = Student::where('StudentNo', $decrypted_id)->firstOrFail();
        $student2 = Student2::where('StudentNo', $decrypted_id)->firstOrFail(); 
        $registration = Registration::where('StudentNo', $decrypted_id)
        ->orderBy('SchoolYear', 'desc')
        ->orderBy('Semester', 'desc')
        ->firstOrFail(); 

        $pageTitle = "Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';
       

        return view('slsu.studentid.processid', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' => $student,
            'student2' => $student2,
            'registration' => $registration
        ]);
    }

    public function getprintpreview(Request $request, StudentId $pdfService)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);

        $student = Student::where('StudentNo', $decrypted_id)->firstOrFail();
        $student2 = Student2::where('StudentNo', $decrypted_id) ->firstOrFail(); 
        $registration = Registration::where('StudentNo', $decrypted_id)
        ->orderBy('SchoolYear', 'desc')
        ->orderBy('Semester', 'desc')
        ->firstOrFail(); 

        $pageTitle = "Preview Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';

        $pdfService->generatePDF($decrypted_id, $student, $student2, $registration);
         
        return view('slsu.studentid.printpreview', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' =>  $student,
            'student2' =>  $student2,
            'registration' => $registration
        ]);
    }   

    public function print(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);

        $fileName = $decrypted_id . '.pdf';
        $pdfPath = public_path('storage/student_id/'. $fileName);
        $printerName = 'Evolis Primacy'; 

        $command = "lp -d $printerName $pdfPath";
        exec($command);

        return response()->json(['message' => 'Printing started']);
    }
}
