<?php

namespace App\Http\Controllers\SLSU\Scholarship;

// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;


class PDFController
{
    public function scholarshipCertificate(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->scholar_id);
            $enrollmentId = Crypt::decryptString($request->enrollment_id);
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Invalid Hash'], 400);
        }

        $schoolYear = $request->school_year;
        $semester = $request->semester;

        $pdf = new ScholarshipCertificate('P', 'mm', 'A4');
        $pdf->setId($id);
        $pdf->setSchEnrollmentId($enrollmentId);
        $pdf->setSchoolYear($schoolYear);
        $pdf->setSemester($semester);

        $pdf::setHeaderCallback(function ($p) use ($pdf) {
            $pdf->Header();
        });

        $pdf::setFooterCallback(function ($p) use ($pdf) {
            $pdf->Footer();
        });

        $pdf::AddPage();
        $pdf::SetMargins(2.54, 2.54, 2.54);
        $pdf::SetAutoPageBreak(true, 2.54);
        $pdf->content();

        $studentName = str_replace(' ', '-', $pdf->getStudentName());
        $date = date("Y-m-d-h-i-s");
        $fileName = "Certificate-of-Scholarship-" . $studentName . "-" . $date . ".pdf";
        $directoryPath = 'scholarship/' . session('campus');

        if (!Storage::exists("public/$directoryPath")) {
            Storage::makeDirectory("public/$directoryPath");
        }

        $filePath = storage_path("app/public/$directoryPath/$fileName");
        $pdf::Output($filePath, 'I');

        return response()->download($filePath);
    }

    public function scholarshipProfileForm(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->scholar_id);
            $enrollmentId = Crypt::decryptString($request->enrollment_id);
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Invalid Hash'], 400);
        }

        $schoolYear = $request->school_year;
        $semester = $request->semester;


        $pdf = new ScholarshipProfileForm('P', 'mm', array(210, 297));
        $pdf->setId($id);
        $pdf->setSchEnrollmentId($enrollmentId);
        $pdf->setSchoolYear($schoolYear);
        $pdf->setSemester($semester);

        $pdf::setHeaderCallback(function ($p) use ($pdf) {
            $pdf->Header();
        });

        $pdf::setFooterCallback(function ($p) use ($pdf) {
            $pdf->Footer();
        });

        $pdf::AddPage();
        $pdf::SetMargins(2.54, 2.54, 2.54);
        $pdf::SetAutoPageBreak(true, 2.54);
        $pdf->content();

        $studentName = str_replace(' ', '-', $pdf->getStudentName());
        $date = date("Y-m-d-h-i-s");
        $fileName = "Certificate-of-Scholarship-" . $studentName . "-" . $date . ".pdf";
        $directoryPath = 'scholarship/' . session('campus');

        if (!Storage::exists("public/$directoryPath")) {
            Storage::makeDirectory("public/$directoryPath");
        }

        $filePath = storage_path("app/public/$directoryPath/$fileName");
        $pdf::Output($filePath, 'I');

        return response()->download($filePath);
    }

    public function scholarshipApplicationForm(Request $request) {}
}
