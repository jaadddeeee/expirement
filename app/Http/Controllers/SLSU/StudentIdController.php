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


    public function getaddnewid()
    {
        $pageTitle = "Add New ID";
        return view('slsu.studentid.addnewid', [
            'pageTitle' => $pageTitle
        ]);
    }

    public function getprocessid($id)
    {
        $encrypted_id = Crypt::decryptString($id);
        
        $student = Student::where('StudentNo', $encrypted_id)->firstOrFail();
        $student2 = Student2::where('StudentNo', $encrypted_id)->firstOrFail(); 
        $registration = Registration::where('StudentNo', $encrypted_id)
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

    public function getprintpreview($id)
    {
        $encrypted_id = Crypt::decryptString($id);

        $student = Student::where('StudentNo', $encrypted_id)->firstOrFail();
        $student2 = Student2::where('StudentNo', $encrypted_id) ->firstOrFail(); 
        $registration = Registration::where('StudentNo', $encrypted_id)
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

    public function generatePDF($id)
    {
        $encrypted_id = Crypt::decryptString($id);

        $student = Student::where('StudentNo', $encrypted_id)->firstOrFail();  

        $pdf = new TCPDF();

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $pdf->AddPage();
        
        $pdf->Image(public_path('images/front.png'), 0, 0, 210, 297, 'PNG');
       
        $pdf->SetFont('trajanpro', '', 26);
        $pdf->SetXY(68, 31.35); 
        $pdf->Cell(30,5,'Southern Leyte',0, 0,'L');
        $pdf->SetFont('trajanpro', '', 21);
        $pdf->SetXY(68, 40.35); 
        $pdf->Cell(30,5,"State University",0,0,'L');
        $pdf->SetFont('helvetica', '', 14);  
        $pdf->SetXY(68, 48.35); 
        $pdf->Cell(0, 1, 'Main Campus | San Roque, Sogod, Southern Leyte', 0, 0);        

        
        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 105; 
        $imageHeight = 100; 

        $xPosition = ($pageWidth - $imageWidth) / 2; 

        $topMargin = 63; 

        $yPosition = $topMargin; 

     
        $pdf->SetLineWidth(1); 
        $pdf->Rect($xPosition, $yPosition, $imageWidth, $imageHeight); 

        $pdf->Image(public_path('images/face-male.jpg'), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
        
        $studentName = strtoupper($student->FirstName) . ' ' . strtoupper(Str::substr($student->MiddleName, 0, 1) . '.') . ' ' . strtoupper($student->LastName);

     
        $pdf->SetFont('helvetica', 'B', 35); 


        $pageWidth = $pdf->getPageWidth();

        $textWidth = $pdf->GetStringWidth($studentName);

        $xPosition = ($pageWidth - $textWidth) / 2;

        $pdf->SetXY($xPosition, 180.35);

        $pdf->Cell($textWidth, 1, $studentName, 0, 0, 'C');

        $course = $student->Course;
       
        $pdf->SetFont('helvetica', '', 19); 

        $pageWidth = $pdf->getPageWidth();

        $textWidth = $pdf->GetStringWidth($studentName);

        $xPosition = ($pageWidth - $textWidth) / 2;

        $pdf->SetXY($xPosition, 197.35);

        $pdf->Cell($textWidth, 1, $course, 0, 0, 'C');

        $pdf->SetTextColor(255, 255, 255);

        $pdf->SetFont('helvetica', '', 14);  
        $pdf->SetXY(6, 246.35); 

        $pdf->Cell(0, 1, 'STUDENT NO:', 0, 0);

        $pdf->SetFont('helvetica', 'B', 50);  
        $pdf->SetXY(6, 251.35); 

        $student_id = $student->StudentNo;

        $pdf->Cell(0, 1, $student_id , 0, 0);


    
        $pdf->AddPage();
     
        $pdf->Image(public_path('images/back.png'), 0, 0, 210, 297, 'PNG'); 

        $pdf->Output('student_id_card.pdf', 'I');
    }

}
