<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;



use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\ScholarshipNew;
use App\Models\ScholarDetail;
use App\Models\ScholarEnrollment;

use GENERAL;

class ScholarshipCertificate extends TCPDF
{
    protected $id;
    protected $letter;
    protected $studentName;
    protected $courseYear;
    protected $studentNo;
    protected $scholarship;
    protected $semester;
    protected $schoolYear;
    protected $date;
    protected $schEnrollmentId;

    public function __construct()
    {
        $this->letter = new LetterHead();
    }

    public function generate()
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                throw new \Exception('Invalid campus database connection');
            }

            $scholar = DB::connection($campus)
                ->table('sch_scholar_details')
                ->join('students', 'sch_scholar_details.student_no', '=', 'students.StudentNo')
                ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                ->join('sch_scholarships', 'sch_scholar_details.scholarship_id', '=', 'sch_scholarships.id')
                ->where('sch_scholar_details.id', $this->getId())
                ->where('sch_scholar_enrollments.id', $this->getSchEnrollmentId())
                ->where('sch_scholar_enrollments.school_year', $this->getSchoolYear())
                ->where('sch_scholar_enrollments.semester', $this->getSemester())
                ->select(
                    'sch_scholar_details.*',
                    'students.FirstName',
                    'students.MiddleName',
                    'students.LastName',
                    'students.Course',
                    'students.StudentYear',
                    'sch_scholarships.sch_name AS scholarship_name',
                    'sch_scholar_enrollments.id AS enrollment_id',
                    'sch_scholar_enrollments.semester',
                    'sch_scholar_enrollments.school_year'
                )
                ->first();

            if (!$scholar) {
                throw new \Exception('Scholar not found');
            }

            $this->studentName = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '') . ' ' . $scholar->LastName;
            $this->courseYear = $scholar->Course . ' - ' . $scholar->StudentYear;
            $this->studentNo = $scholar->student_no;
            $this->scholarship = $scholar->scholarship_name ?? 'N/A';
            $this->semester = GENERAL::Semesters()[$scholar->semester]['Short'];
            $this->schoolYear = GENERAL::setSchoolYearLabel($scholar->school_year, $scholar->semester);
            $this->date = date('F j, Y');
        } catch (DecryptException $e) {
            throw new \Exception('Invalid or corrupted scholarship ID');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function Header()
    {
        $this->letter->ReportHeader();

        $startY = 42;

        $pageWidth = $this::GetPageWidth();

        $this::SetFont('cambria', 'B', 12);
        $textWidth1 = $this::GetStringWidth("OFFICE OF STUDENTS AND AUXILIARY SERVICES");

        $centerX1 = ($pageWidth - $textWidth1) / 2;

        $this::setXY($centerX1, $startY);
        $this::Cell($textWidth1, 5, "OFFICE OF STUDENTS AND AUXILIARY SERVICES", 0, 1, 'C');

        $startY += 10;
        $this::SetFont('cambria', 'B', 12);
        $textWidth2 = $this::GetStringWidth("STUDENTS'S SCHOLARSHIP/GRANT CERTIFICATION");

        $centerX2 = ($pageWidth - $textWidth2) / 2;

        $this::setXY($centerX2, $startY);
        $this::Cell($textWidth2, 4, "STUDENTS'S SCHOLARSHIP/GRANT CERTIFICATION", 0, 1, 'C');
    }

    public function content()
    {
        $this->generate();
        $startY = 65;
        $this::setXY(30, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "TO WHOM IT MAY CONCERN:", 0, 1, 'L');

        $startY += 10;
        $this::setXY(43, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "THIS IS TO CERTIFY", 0, 0, 'L');

        $this::setXY(82, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, " that ", 0, 0, 'L');

        // Calculate the widths of the student name and course year
        $nameWidth = $this::GetStringWidth($this->studentName);
        $courseWidth = $this::GetStringWidth($this->courseYear);

        // Length of the underline
        $underlineLength = 60;

        // Center student name over underline
        $nameX = 84 + (($underlineLength - $nameWidth) / 2);
        $courseX = 129 + (($underlineLength - $courseWidth) / 2);

        // Draw underlines
        $this::setXY(92, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(70, 10, str_repeat('_', 30), 0, 0, 'L');
        $this::setXY(141, $startY);
        $this::Cell(70, 10, str_repeat('_', 25), 0, 0, 'L');

        $this::setXY($nameX, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->studentName}", 0, 0, 'L');

        $this::setXY($courseX, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->courseYear}", 0, 0, 'L');

        $startY += 5;
        $this::setXY(108, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "(Name)", 0, 0, 'L');

        $this::setXY(145, $startY);
        $this::Cell(0, 10, "(Course/Year Level)", 0, 0, 'L');

        $startY += 10;
        $this::setXY(31, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "with Student No.", 0, 0, 'L');

        $studentNoWidth = $this::GetStringWidth($this->studentNo);
        $scholarshipWidth = $this::GetStringWidth($this->scholarship);

        $underlineLength = 50;

        $studentNoX = 50 + (($underlineLength - $studentNoWidth) / 2);
        $scholarshipX = 120 + (($underlineLength - $scholarshipWidth) / 2);

        $this::setXY(63, $startY);
        $this::Cell($underlineLength, 10, str_repeat('_', 16), 0, 0, 'L');

        $this::setXY(116, $startY);
        $this::Cell($underlineLength, 10, str_repeat('_', 40), 0, 0, 'L');

        $this::setXY($studentNoX, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->studentNo}", 0, 0, 'L');

        $this::setXY(89, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "is qualified for", 0, 0, 'L');

        $this::setXY($scholarshipX, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->scholarship}", 0, 0, 'L');



        $startY += 10;
        $this::setXY(31, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "scholarship/grant", 0, 0, 'L');

        $this::setXY(65, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "this ", 0, 0, 'L');

        $this::setXY(73, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->semester}", 0, 0, 'L');

        $this::setXY(81, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, " semester of SY ", 0, 0, 'L');

        $this::setXY(110, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "{$this->schoolYear}.", 0, 0, 'L');

        $day = date('j');
        $month = date('F');
        $year = date('Y');

        function getDayWithSuffix($day)
        {
            if ($day >= 11 && $day <= 13) {
                return $day . 'th';
            }
            switch ($day % 10) {
                case 1:
                    return $day . 'st';
                case 2:
                    return $day . 'nd';
                case 3:
                    return $day . 'rd';
                default:
                    return $day . 'th';
            }
        }

        $dayWithSuffix = getDayWithSuffix($day);

        $startY += 10;
        $this::setXY(40, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "Signed at ", 0, 0, 'L');

        $this::setXY(58, $startY);
        $this::Cell(65, 10, str_repeat('_', 35), 0, 0, 'L');

        $this::setXY(113, $startY);
        $this::Cell(10, 10, " this ", 0, 0, 'L');

        $this::setXY(122, $startY);
        $this::Cell(65, 10, str_repeat('_', 6), 0, 0, 'L');

        $this::setXY(132, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(10, 10, "day of", 0, 0, 'L');

        $this::setXY(144, $startY);
        $this::Cell(65, 10, str_repeat('_', 15), 0, 0, 'L');


        $this::setXY(168, $startY);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(20, 10,  " {$year}", 0, 1, 'L');


        $startY += 30;
        $this::setXY(30, $startY);
        $this::SetFont('cambria', '', 12);

        $this::Cell(0, 10, "Recommending Approval:                                                   Approved by: ", 0, 1, 'L');

        $startY += 15;
        $this::setXY(30, $startY);
        $this::Cell(0, 10, "_______________________________                                                 ___________________________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(30, $startY);
        $this::Cell(0, 10, "  Scholarship Coordinator                                                                   Director, OSAS", 0, 1, 'L');
    }

    public function Footer()
    {
        $this->letter->ReportFooter(['QC' => config('QC.SS06')]);
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getSchEnrollmentId()
    {
        return $this->schEnrollmentId;
    }

    public function setSchEnrollmentId($schEnrollmentId)
    {
        $this->schEnrollmentId = $schEnrollmentId;
        return $this;
    }

    public function getStudentName()
    {
        return $this->studentName;
    }

    public function setStudentName($studentName)
    {
        $this->studentName = $studentName;
        return $this;
    }

    public function getCourseYear()
    {
        return $this->courseYear;
    }

    public function setCourseYear($courseYear)
    {
        $this->courseYear = $courseYear;
        return $this;
    }

    public function getStudentNo()
    {
        return $this->studentNo;
    }

    public function setStudentNo($studentNo)
    {
        $this->studentNo = $studentNo;
        return $this;
    }

    public function getScholarship()
    {
        return $this->scholarship;
    }

    public function setScholarship($scholarship)
    {
        $this->scholarship = $scholarship;
        return $this;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    public function getSchoolYear()
    {
        return $this->schoolYear;
    }

    public function setSchoolYear($schoolYear)
    {
        $this->schoolYear = $schoolYear;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
}
