<?php

namespace App\Services;

use TCPDF;
use Carbon\Carbon;
use Illuminate\Support\Str;
use GENERAL;

class StudentId
{
    public function generatePDF($decrypted_id, $student, $student2, $registration)
    {
        $pdf = new TCPDF('P', 'mm', array(54.86, 86.01), true, 'UTF-8', false);

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $pdf->Image(public_path('images/student/front.png'), 0, 0, 54.86, 86.01, 'PNG');

        $pdf->SetFont('trajanpro', '', 7);
        $pdf->SetXY(16.8, 8.2);
        $pdf->Cell(30, 5, 'Southern Leyte', 0, 0, 'L');

        $pdf->SetFont('trajanpro', '', 5.7);
        $pdf->SetXY(16.9, 10.5);
        $pdf->Cell(30, 5, 'State University', 0, 0, 'L');

        $pdf->SetFont('poppins', '', 3.5);
        $pdf->SetXY(17, 14);
        $pdf->Cell(0, 1, 'Main Campus | San Roque, Sogod, Southern Leyte', 0, 0);

        $imageWidth = 28;
        $imageHeight = 28;
        $xPosition = (54.86 - $imageWidth) / 2;
        $yPosition = 18;

        $pdf->SetLineWidth(0.2);
        $pdf->Rect($xPosition, $yPosition, $imageWidth, $imageHeight);

        if (!empty($student->Picture)) {
            $image = $student->Picture;
        } else if ($student->Sex === 'Male') {
            $image = 'images/face-male.jpg';
        } else if ($student->Sex === 'Female') {
            $image = 'images/face-female.jpg';
        }

        $pdf->Image(public_path($image), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);

        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 26.92;
        $imageHeight = 7.72;

        $xPosition = ($pageWidth - $imageWidth) / 2;

        $topMargin = 46.5;

        $yPosition = $topMargin;

        $signaturePath = 'storage/student_id_signature/' . $student->StudentNo . '.png';

        if (file_exists(public_path($signaturePath))) {
            $signature = $signaturePath;
        } else {
            $signature = 'images/signature.png';
        }

        $pdf->Image(public_path($signature), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);

        $studentName = strtoupper($student->FirstName) . ' ' . strtoupper(Str::substr($student->MiddleName, 0, 1) . '.') . ' ' . strtoupper($student->LastName);

        $pdf->SetFont('poppins', 'B', 9.5);
        $textWidth = $pdf->GetStringWidth($studentName);
        $xPosition = (54.86 - $textWidth) / 2;
        $pdf->SetXY($xPosition, 53);
        $pdf->Cell($textWidth, 1, $studentName, 0, 0, 'C');

        $pdf->SetFont('poppins', '', 5.8);
        $pdf->SetXY(0, 58);
        $pdf->Cell(0, 1, $student->Course, 0, 0, 'C');

        $pdf->SetFont('poppins', 'B', 5.8);
        $pdf->SetXY(0, 60.8);
        $pdf->Cell(0, 1, $student->major, 0, 0, 'C');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(1, 72);
        $pdf->Cell(0, 1, 'STUDENT NO:', 0, 0);

        $pdf->SetFont('poppins', 'B', 11.5);
        $pdf->SetXY(1, 74.5);
        $pdf->Cell(0, 1, $student->StudentNo, 0, 0);

        $pdf->SetTextColor(0, 0, 0);

        $boxWidth = 26;
        $boxHeight = 8;
        $x = 55.86 - $boxWidth - 3;
        $y = 71.5;

        $pdf->SetFillColor(255, 255, 255);
        $pdf->RoundedRect($x, $y, $boxWidth, $boxHeight, 1, '1111', 'F');

        $pdf->SetFont('poppins', 'B', 10);
        $pdf->SetXY($x + -0.1, $y + 0.3);
        $pdf->Cell(0, 1, 'ENROLLED', 0, 0);

        $semesters = GENERAL::Semesters();
        $semesterShort = $semesters[$registration->Semester]
            ? $semesters[$registration->Semester]['Short']
            : 'N/A';

        $schoolYearLabel = GENERAL::setSchoolYearLabel(
            $registration->SchoolYear,
            $registration->Semester,
        );

        $pdf->SetFont('poppins', '', 6.8);
        $pdf->SetXY($x + -0.1, $y + 4.5);
        $pdf->Cell(0, 1, $schoolYearLabel . ' - ' . $semesterShort, 0, 0);

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(0, 81);
        $pdf->Cell(0, 1, 'www.southernleytestateu.edu.ph', 0, 0, 'C');

        $pdf->AddPage();
        $pdf->Image(public_path('images/student/back.png'), 0, 0, 54.86, 86.01, 'PNG');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 4);
        $pdf->Cell(30, 5, 'This is to certify that the bearer, whose', 0, 0);

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 6.6);
        $pdf->Cell(30, 5, 'name and photo appear in front is a', 0, 0);


        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 9.6);
        $pdf->Cell(30, 5, 'bonafide student of SLSU.', 0, 0);

        $pageWidth = $pdf->getPageWidth();
        $imageWidth = 13;
        $imageHeight = 13;

        $xPosition = ($pageWidth - $imageWidth) / 1.15;

        $topMargin = 11;

        $yPosition = $topMargin;

        $pdf->SetAlpha(0.5);

        $pdf->Image(public_path($image), $xPosition, $yPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', false, false, 0, false, false, false);

        $pdf->SetAlpha(1);

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 17.2);
        $pdf->Cell(30, 5, 'In case of emergency,', 0, 0);

        $emer_name = strtoupper($student->emer_name);

        $pdf->SetFont('poppins', 'B', 6);
        $pdf->SetXY(6, 20.9);
        $pdf->Cell(30, 5, $emer_name, 0, 0);

        $contact = $student->emer_contact;

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 23.6);
        $pdf->Cell(30, 5, $contact, 0, 0);

        $address = $student->p_street . ', ' . $student->p_municipality . ', ' . $student->p_province;

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 26.3);
        $pdf->Cell(30, 5, $address, 0, 0);

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 32.5);
        $pdf->Cell(30, 5, 'Allergy/ies:', 0, 0);

        $allergy = $student2->Allergy;

        $pdf->SetFont('poppins', 'B', 6);
        $pdf->SetXY(6, 35.3);
        $pdf->Cell(30, 5, $allergy, 0, 0);

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 41.5);
        $pdf->Cell(30, 5, 'Blood Type:', 0, 0);

        $blood_type = $student2->BloodType;

        $pdf->SetFont('poppins', 'B', 6);
        $pdf->SetXY(6, 44.3);
        $pdf->Cell(30, 5, $blood_type, 0, 0);

        $pdf->SetFont('poppins', '', 6);
        $pdf->SetXY(6, 50.5);
        $pdf->Cell(30, 5, 'Date Issued:', 0, 0);

        $date_issued = Carbon::now()->format('l, d F Y');

        $pdf->SetFont('poppins', 'B', 6);
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

        $fileName = $decrypted_id . '.pdf';
        $filePath = public_path('storage/student_id/' . $fileName);

        $pdf->Output($filePath, 'F');
    }
}
