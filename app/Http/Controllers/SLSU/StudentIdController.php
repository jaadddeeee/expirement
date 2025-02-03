<?php

namespace App\Http\Controllers\SLSU;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Student2;
use App\Models\Registration;
use Illuminate\Support\Facades\Crypt;
use TCPDF;

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
        $student2 = Student2::where('StudentNo', $encrypted_id)
        ->firstOrFail(); 

        $pageTitle = "Preview Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';

        return view('slsu.studentid.printpreview', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' =>  $student,
            'student2' =>  $student2,
        ]);
    }

    public function generatePDF($id)
    {
        $encrypted_id = Crypt::decryptString($id);

        $student = Student::where('StudentNo', $encrypted_id)->firstOrFail();  // Fetch student data

        $pdf = new TCPDF();

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $pdf->AddPage();

        $pdf->Image(public_path('images/front.png'), 0, 0, 210, 297, 'PNG'); // Full page size (A4 - 210mm x 297mm)

       
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(68, 29.35); 
        $pdf->Cell(0, 10, strtoupper('Southern Leyte'), 0, 1);
        $pdf->SetXY(68, 34.35); 
        $pdf->Cell(0, 10, strtoupper('State University'), 0, 1);
        $pdf->SetXY(68, 40.35); 
        $pdf->Cell(0, 10, 'Main Campus | San Roque, Sogod, Southern Leyte', 0, 1);

       
        $pdf->AddPage();

     
        $pdf->Image(public_path('images/back.png'), 0, 0, 210, 297, 'PNG'); // Full page size (A4 - 210mm x 297mm)

        $pdf->Output('student_id_card.pdf', 'I');
    }

}
