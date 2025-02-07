<?php

namespace App\Http\Controllers\SLSU;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Student2;
use App\Models\Registration;
use App\Models\DefaultValue;
use Illuminate\Support\Facades\Crypt;
use App\Services\StudentId;

class StudentIdController extends Controller
{

    public function index(Request $request)
    {
        $pageTitle = "School ID";
        $search = $request->query('search'); // Get the search query from the URL
    
        // Fetch students, filtering if a search query is provided
        $student = Student::when($search, function ($query, $search) {
            return $query->where('StudentNo', 'LIKE', "%{$search}%")
                         ->orWhere('FirstName', 'LIKE', "%{$search}%")
                         ->orWhere('MiddleName', 'LIKE', "%{$search}%")
                         ->orWhere('LastName', 'LIKE', "%{$search}%");
        })->get();
    
        return view('slsu.studentid.index', [
            'pageTitle' => $pageTitle,
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
            'registration' => $registration,
        ]);
    }

    public function getprintpreview(Request $request, StudentId $pdfService)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);

        $defaultValues = DefaultValue::whereIn('DefaultName', ['CampusString', 'SchoolAddress', 'PresidentName', 'SchoolWebsite'])
        ->pluck('DefaultValue', 'DefaultName');
    

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
            'registration' => $registration,
            'defaultValues' => $defaultValues,
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
