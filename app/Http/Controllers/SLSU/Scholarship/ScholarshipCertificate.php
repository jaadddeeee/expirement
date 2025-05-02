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
        // Initialize the LetterHead instance
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

        $leftMargin = 25.4; // 1 inch
        $rightMargin = 25.4;
        $pageWidth = $this::GetPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;

        $this::SetFont('cambria', 'B', 12);
        $html1 = '<p>OFFICE OF STUDENTS AND AUXILIARY SERVICES</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html1, 0, 1, false, true, 'C');

        $startY += 10;
        $html2 = '<p>STUDENTS\'S SCHOLARSHIP/GRANT CERTIFICATION</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html2, 0, 1, false, true, 'C');
    }

    public function content()
    {
        $this->generate();
        $startY = 65;

        $leftMargin = 25.4; // 1 inch
        $rightMargin = 25.4;
        $pageWidth = $this::GetPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;

        $html1 = '<p style="font-family: cambria; font-size: 12px; font-weight: bold; line-height: 1.5;">TO WHOM IT MAY CONCERN:</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html1, 0, 1, false, true, 'L');

        $startY += 10;
        $day = date('j');
        $dayWithSuffix = $day . ($day % 10 == 1 && $day != 11 ? 'st' : ($day % 10 == 2 && $day != 12 ? 'nd' : ($day % 10 == 3 && $day != 13 ? 'rd' : 'th')));

        $year = date('Y');

        $html2 = '<p style="font-family: cambria; font-size: 12px; line-height: 30px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <strong>THIS IS TO CERTIFY</strong> that <strong>' . $this->studentName . ' ' . $this->courseYear . '</strong> with <br>Student No. <strong>' . $this->studentNo . '</strong> 
        is qualified for <strong>' . $this->scholarship . ' </strong> scholarship /grant this <strong>' . $this->semester . '</strong> semester of SY <strong>' . $this->schoolYear . '. </strong>
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Signed at _____________________________________ this <strong>' . $dayWithSuffix . '</strong> day of <strong>' . $year . '.</strong></p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html2, 0, 1, false, true, 'L');

        $startY += 60;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "Recommending Approval:                                                  Approved by:", 0, 1, 'L');

        $startY += 15;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, "______________________________                                                   ______________________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 12);
        $this::Cell(0, 10, " Scholarship Coordinator                                                               Director, OSAS", 0, 1, 'L');
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
