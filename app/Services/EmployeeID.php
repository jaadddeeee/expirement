<?php

namespace App\Services;

use TCPDF;
use Carbon\Carbon;
use Illuminate\Support\Str;
use GENERAL;
use AES;
use DB;

class EmployeeID
{
    public function generatePDF($decrypted_id, $employee, $employee2)
    {
        $pdf = new TCPDF('P', 'mm', array(54.86, 86.01), true, 'UTF-8', false);

        $defaultValues = DB::connection(strtolower(session('campus')))
        ->table('defaultvalue')
        ->whereIn('DefaultName', ['CampusString', 'SchoolAddress', 'PresidentName', 'SchoolWebsite'])
        ->pluck('DefaultValue', 'DefaultName');

    
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
    
        $pdf->Image(public_path('images/employee/front.png'), 0, 0, 54.86, 86.01, 'PNG');
    
        $pdf->SetFont('trajanpro', '', 7);
        $pdf->SetXY(16.8, 7.2);
        $pdf->Cell(30, 5, 'Southern Leyte', 0, 0, 'L');
    
        $pdf->SetFont('trajanpro', '', 5.7);
        $pdf->SetXY(16.9, 9.5);
        $pdf->Cell(30, 5, 'State University', 0, 0, 'L');
    
        $pdf->SetFont('poppins', '', 3.5);
        $pdf->SetXY(17, 13);
        $pdf->Cell(0, 1, $defaultValues['CampusString'] . ' | ' . $defaultValues['SchoolAddress'], 0, 0);
    
        $imageWidth = 28;
        $imageHeight = 31.5;
        $xPosition = (54.86 - $imageWidth) / 2;
        $yPosition = 18;
    
        $pdf->SetLineWidth(0.2);
        $pdf->Rect($xPosition, $yPosition, $imageWidth, $imageHeight);

        $decryptedSex = AES::decrypt($employee->Sex);
        if (!empty($employee->profilephoto)) {
            $image = $employee->profilephoto;
        } else if ($decryptedSex === 'Male') {
            $image = 'images/face-male.jpg';
        } else if ($decryptedSex === 'Female') {
            $image = 'images/face-female.jpg';
        } else if ($decryptedSex === '') {
            $image = 'images/user.png';
        }
    
        $pdf->Image(public_path($image), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
      
        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 26.92; 
        $imageHeight = 8.72; 
        
        $xPosition = ($pageWidth - $imageWidth) / 2; 
        
        $topMargin = 50; 
        
        $yPosition = $topMargin; 

        
        $signaturePath = 'storage/employee_id_signature/' . $employee->AgencyNumber . '.png';

        if (file_exists(public_path($signaturePath))) {
            $signature = $signaturePath;
        } else {
            $signature = 'images/signature.png';
        }

        $pdf->Image(public_path($signature), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
        
        $employeeName = strtoupper($employee->FirstName) . ' ' . strtoupper(Str::substr($employee->MiddleName, 0, 1) . '.') . ' ' . strtoupper($employee->LastName);
    
        $pdf->SetFont('poppins', 'B', 11.5);
        $textWidth = $pdf->GetStringWidth($employeeName);
        $xPosition = (54.86 - $textWidth) / 2;
        $pdf->SetXY($xPosition, 58);
        
        // Draw the text
        $pdf->Cell($textWidth, 1, $employeeName, 0, 0, 'C');
        
        $pdf->SetLineWidth(0.4); 
        $pdf->Line($xPosition, 61.5 + 1, $xPosition + $textWidth, 61.5 + 1); // Draw the line under the text
        
    
        $pdf->SetFont('poppins', '', 8);
        $pdf->SetXY(1, 63);
        $pdf->Cell(0, 1, 'Staff', 0, 0, 'C');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(2, 71);
        $pdf->Cell(0, 1, 'EMPLOYEE NO.', 0, 0);
    
        $pdf->SetFont('poppins', 'B', 11.5);
        $pdf->SetXY(2, 73.5);
        $pdf->Cell(0, 1, $employee->AgencyNumber, 0, 0);
    
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(0, 81);
        $pdf->Cell(0, 1, 'www.southernleytestateu.edu.ph', 0, 0, 'C');
    
        $pdf->AddPage();
        $pdf->Image(public_path('images/employee/back.png'), 0, 0, 54.86, 86.01, 'PNG');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(6, 3.6);
        $pdf->Cell(30, 5, 'This is to certify that the bearer of this', 0, 0);

        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(6, 6.6);
        $pdf->Cell(30, 5, 'identification card, whose name and photo', 0, 0);

        
        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(6, 9.6);
        $pdf->Cell(30, 5, 'appear in front, is an employee of', 0, 0);

        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(6, 12.6);
        $pdf->Cell(30, 5, 'Southern Leyte State University.', 0, 0);
    
        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 10; 
        $imageHeight = 10; 
        
        $xPosition = ($pageWidth - $imageWidth) / 1.15; 
        
        $topMargin = 13.6; 
        
        $yPosition = $topMargin; 

        $pdf->SetAlpha(0.5);

        $pdf->Image(public_path($image), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);
     
        $pdf->SetAlpha(1);

        $pdf->SetFont('poppins', '', 5.5);
        $pdf->SetXY(6, 17.2);
        $pdf->Cell(30, 5, 'In case of emergency,', 0, 0);

        $emer_name = $employee2->name ?? 'N/A';

        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 20.9);
        $pdf->Cell(30, 5, $emer_name , 0, 0);

        $emer_address = $employee2->address ?? 'N/A';

        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 23.6);
        $pdf->Cell(30, 5, $emer_address, 0, 0);
    
       $contact = $employee2->number ?? 'N/A';
        
        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 26.3);
        $pdf->Cell(30, 5, $contact, 0, 0);

        $pdf->SetFont('poppins', '',5.5);
        $pdf->SetXY(6, 32.5);
        $pdf->Cell(30, 5, 'Allergy/ies:', 0, 0);

        $allergy = $employee->Allergies ?? 'N/A';

        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 35.3);
        $pdf->Cell(30, 5, $allergy, 0, 0);

        $pdf->SetFont('poppins', '',5.5);
        $pdf->SetXY(6, 41.5);
        $pdf->Cell(30, 5, 'Blood Type:', 0, 0);
        
        $blood_type = $employee->BloodType ?? 'N/A';

        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 44.3);
        $pdf->Cell(30, 5, $blood_type, 0, 0);

        $pdf->SetFont('poppins', '',5.5);
        $pdf->SetXY(6, 50.5);
        $pdf->Cell(30, 5, 'Date Issued:', 0, 0);

        $date_issued = Carbon::now()->format('l, d F Y');

        $pdf->SetFont('poppins', 'B',5.5);
        $pdf->SetXY(6, 53.3);
        $pdf->Cell(30, 5, $date_issued, 0, 0);

        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 5; 
        $imageHeight = 5; 
        
        $xPosition = ($pageWidth - $imageWidth) / 2; 
        
        $topMargin = 65.5; 
        
        $yPosition = $topMargin; 

        $pdf->Image(public_path('images/e_sig_jude.png'), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);

        $pdf->SetFont('poppins', 'B', 7.7);
        $pdf->SetXY(0, 69.3);
        $pdf->Cell(0, 5, 'JUDE A. DUARTE, DPA', 0, 0, 'C');

        $pdf->SetFont('poppins', '', 4.5);
        $pdf->SetXY(0, 72.3);
        $pdf->Cell(0, 5, 'University President', 0, 0, 'C');

        $fileName = $employee->AgencyNumber . '.pdf';
        $filePath = public_path('storage/employee_id/' . $fileName);


        $pdfContent = $pdf->Output($filePath, 'F'); 
    }   
}
