<?php

namespace App\Http\Controllers\SLSU;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Student2;
use App\Models\Registration;
use Illuminate\Support\Facades\Crypt;
use TCPDF;
use Illuminate\Support\Str;
use GENERAL;


class StudentIdController extends Controller
{

    public function index()
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

    public function getprintpreview(Request $request)
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

        return view('slsu.studentid.printpreview', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' =>  $student,
            'student2' =>  $student2,
            'registration' => $registration
        ]);
    }

    public function generatePDF(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);
    
        $student = Student::where('StudentNo', $decrypted_id)->firstOrFail();  
        $registration = Registration::where('StudentNo', $decrypted_id)
            ->orderBy('SchoolYear', 'desc')
            ->orderBy('Semester', 'desc')
            ->firstOrFail(); 
    
        // Set custom page size (54.86 mm × 86.01 mm)
        $pdf = new TCPDF('P', 'mm', array(54.86, 86.01), true, 'UTF-8', false);
    
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
    
        // Background Image (Front Side)
        $pdf->Image(public_path('images/front.png'), 0, 0, 54.86, 86.01, 'PNG');
    
        // University Name & Address
        $pdf->SetFont('trajanpro', '', 7);
        $pdf->SetXY(16.8, 8.2);
        $pdf->Cell(30, 5, 'Southern Leyte', 0, 0, 'L');
    
        $pdf->SetFont('trajanpro', '', 5.7);
        $pdf->SetXY(16.9, 10.5);
        $pdf->Cell(30, 5, 'State University', 0, 0, 'L');
    
        $pdf->SetFont('helvetica', '', 3.8);
        $pdf->SetXY(17, 14);
        $pdf->Cell(0, 1, 'Main Campus | San Roque, Sogod, Southern Leyte', 0, 0);
    
        // Profile Picture Placeholder
        $imageWidth = 28;
        $imageHeight = 28;
        $xPosition = (54.86 - $imageWidth) / 2;
        $yPosition = 18;
    
        $pdf->SetLineWidth(0.5);
        $pdf->Rect($xPosition, $yPosition, $imageWidth, $imageHeight);
    
        $pdf->Image(public_path('images/face-male.jpg'), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
      
        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 26.92; 
        $imageHeight = 8.72; 
        
        $xPosition = ($pageWidth - $imageWidth) / 2; 
        
        $topMargin = 46.5; 
        
        $yPosition = $topMargin; 

        $pdf->Image(public_path('images/signature.png'), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
        

        // Student Name
        $studentName = strtoupper($student->FirstName) . ' ' . strtoupper(Str::substr($student->MiddleName, 0, 1) . '.') . ' ' . strtoupper($student->LastName);
    
        $pdf->SetFont('helvetica', 'B', 9.5);
        $textWidth = $pdf->GetStringWidth($studentName);
        $xPosition = (54.86 - $textWidth) / 2;
        $pdf->SetXY($xPosition, 53);
        $pdf->Cell($textWidth, 1, $studentName, 0, 0, 'C');
    
        // Course & Major
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetXY(0, 58);
        $pdf->Cell(0, 1, $student->Course, 0, 0, 'C');
    
        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->SetXY(0, 62);
        $pdf->Cell(0, 1, $student->major, 0, 0, 'C');
    
        // Student Number
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 4.5);
        $pdf->SetXY(0.5, 71.5);
        $pdf->Cell(0, 1, 'STUDENT NO:', 0, 0);
    
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(0.5, 73.5);
        $pdf->Cell(0, 1, $student->StudentNo, 0, 0);
    
        // Enrollment Status Box
        $pdf->SetTextColor(0, 0, 0);
        $school_year = $registration->SchoolYear;
        $semesters = GENERAL::Semesters();
        $semesterShort = $semesters[$registration->Semester]
            ? $semesters[$registration->Semester]['Short']
            : 'N/A';
        
        $boxWidth = 26;
        $boxHeight = 8;
        $x = 55.86 - $boxWidth - 3;
        $y = 71.5;
    
        $pdf->SetFillColor(255, 255, 255);
        $pdf->RoundedRect($x, $y, $boxWidth, $boxHeight, 1, '1111', 'F');
    
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x + -0.1, $y + 0.3);
        $pdf->Cell(0, 1, 'ENROLLED', 0, 0);
    
        $pdf->SetFont('helvetica', '', 5.9);
        $pdf->SetXY($x + -0.1, $y + 4);
        $pdf->Cell(0, 1, $school_year . ' - ' . $semesterShort, 0, 0);

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetXY(0, 81);
        $pdf->Cell(0, 1, 'www.southernleytestateu.edu.ph', 0, 0, 'C');
    
        $pdf->AddPage();
        $pdf->Image(public_path('images/back.png'), 0, 0, 54.86, 86.01, 'PNG');
    
        $pdf->Output('student_id_card.pdf', 'I');
    }
    
    
}
